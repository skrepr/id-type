<?php

declare(strict_types=1);

namespace Skrepr\IdType\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    private const TREE_BUILDER = 'skrepr_id_type';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        return new TreeBuilder(self::TREE_BUILDER);
    }
}
