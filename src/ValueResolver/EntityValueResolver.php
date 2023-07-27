<?php

declare(strict_types=1);

namespace Skrepr\IdType\ValueResolver;

use Skrepr\IdType\ValueObject\AbstractUuid;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\ManagerRegistry;
use ReflectionNamedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

class EntityValueResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly ManagerRegistry $registry,
    ) {
    }

    /**
     * @return array<int, object|null>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        // first check to see if argument is of a class-type
        $argumentType = $argument->getType() ?? '';
        if (!class_exists($argumentType)) {
            return [];
        }

        // check if the requested class is in the entity manager
        $manager = $this->registry->getManagerForClass($argumentType);
        if ($manager === null) {
            return [];
        }

        // check if primary key is of type AbstractUuid
        $meta = $manager->getClassMetadata($argumentType);
        if ($meta instanceof ClassMetadataInfo) {
            $reflectionIdType = $meta->getSingleIdReflectionProperty()->getType();
        }
        if (isset($reflectionIdType) && $reflectionIdType instanceof ReflectionNamedType) {
            $idType = $reflectionIdType->getName();
        }
        if (
            !isset($idType)
            || !is_subclass_of($idType, AbstractUuid::class, true)
        ) {
            return [];
        }

        // get the value from the request, based on the argument name
        $value = $request->attributes->get($argument->getName());
        if (!is_string($value)) {
            return [];
        }

        if (!Uuid::isValid($value)) {
            throw new NotFoundHttpException(sprintf('The uid for the "%s" parameter is invalid.', $argument->getName()));
        }

        $object = $manager->find($argumentType, $idType::fromString($value));
        if ($object === null && !$argument->isNullable()) {
            throw new NotFoundHttpException(sprintf('"%s" object not found by "%s".', $argumentType, self::class));
        }

        return [$object];
    }
}
