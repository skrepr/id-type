<?php

declare(strict_types=1);

namespace Skrepr\IdType\DependencyInjection;

use Skrepr\IdType\Persistence\Doctrine\AbstractUuidType;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AutoRegisterTypesPass implements CompilerPassInterface {
    private const CONTAINER_TYPES_PARAMETER = 'doctrine.dbal.connection_factory.types';

    public function __construct(
        private readonly string $tag,
    ) {
    }

    public function process(ContainerBuilder $container): void
    {
        $typeDefinition = $container->getParameter(self::CONTAINER_TYPES_PARAMETER);
        $taggedServices = $container->findTaggedServiceIds($this->tag);

        $types = $this->generateTypes(array_keys($taggedServices));

        foreach ($types as $type) {
            $name = $type['name'];
            $namespace = $type['namespace'];

            if (array_key_exists($name, $typeDefinition)) {
                continue;
            }

            $typeDefinition[$name] = ['class' => $namespace];
        }

        $container->setParameter(self::CONTAINER_TYPES_PARAMETER, $typeDefinition);
    }

    private function generateTypes(array $classes): iterable
    {
        foreach ($classes as $className) {
            if (!is_subclass_of($className, AbstractUuidType::class)) {
                continue;
            }
            $idType = new $className;
            $idType->getName();

            yield [
                'namespace' => $className,
                'name' => $idType->getName(),
            ];
        }
    }
}
