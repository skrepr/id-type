<?php

declare(strict_types=1);

namespace Skrepr\IdType;

use Skrepr\IdType\DependencyInjection\AutoRegisterTypesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SkreprIdTypeBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AutoRegisterTypesPass('skrepr.id-type'));

        parent::build($container);
    }
}
