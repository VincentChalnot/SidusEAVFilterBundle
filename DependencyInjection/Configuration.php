<?php

namespace Sidus\EAVFilterBundle\DependencyInjection;

use Sidus\FilterBundle\DependencyInjection\Configuration as BaseConfiguration;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link
 * http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration extends BaseConfiguration
{
    /**
     * @param NodeBuilder $filterDefinition
     */
    protected function appendFilterDefinition(NodeBuilder $filterDefinition)
    {
        parent::appendFilterDefinition($filterDefinition);

        $filterDefinition
            ->scalarNode('family')->isRequired()->end()
            ->scalarNode('entity')->defaultNull()->end();
    }
}
