<?php

namespace Bpi\ApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class BpiConfiguration.
 *
 * @package Bpi\ApiBundle\DependencyInjection
 */
class BpiConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bpi_api');

        // TODO: Define custom config entries.

        return $treeBuilder;
    }
}
