<?php

declare(strict_types=1);

namespace Skrepr\IdType\DependencyInjection;

use Skrepr\IdType\Maker\IdTypeMaker;
use Skrepr\IdType\ValueResolver\EntityValueResolver;
use Skrepr\IdType\ValueResolver\IdValueResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SkreprIdTypeExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->addAnnotatedClassesToCompile([
            IdTypeMaker::class,
        ]);

        $container->register('skrepr_id_type.value_resolver.entity', EntityValueResolver::class)
            ->setAutowired(true)
            ->addTag('controller.argument_value_resolver', ['priority' => 200 ]);

        $container->register('skrepr_id_type.value_resolver.id', IdValueResolver::class)
            ->setAutowired(true)
            ->addTag('controller.argument_value_resolver', ['priority' => 210 ]);

        $container->register('skrepr_id_type.maker.id_type', IdTypeMaker::class)
            ->setAutowired(true)
            ->addTag('maker.command');
    }

    public function getAlias(): string
    {
        return 'skrepr_id_type';
    }
}
