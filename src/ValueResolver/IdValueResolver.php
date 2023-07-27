<?php

declare(strict_types=1);

namespace Skrepr\IdType\ValueResolver;

use Skrepr\IdType\ValueObject\AbstractUuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

class IdValueResolver implements ValueResolverInterface
{
    /**
     * @return array<int, AbstractUuid|null>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();

        if (
            $argumentType === null
            || !is_subclass_of($argumentType, AbstractUuid::class, true)
        ) {
            return [];
        }

        // get the value from the request, based on the argument name
        $value = $request->attributes->get($argument->getName());
        if ($value === null && $argument->isNullable()) {
            return [null];
        }

        if ($value === null) {
            throw new NotFoundHttpException(sprintf('The uid for the "%s" parameter can not be NULL.', $argument->getName()));
        }
        if (!Uuid::isValid($value)) {
            throw new NotFoundHttpException(sprintf('The uid for the "%s" parameter is invalid.', $argument->getName()));
        }

        // create and return the value object
        return [$argumentType::fromString($value)];
    }
}
