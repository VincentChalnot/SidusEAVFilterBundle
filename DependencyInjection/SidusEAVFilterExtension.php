<?php

namespace Sidus\EAVFilterBundle\DependencyInjection;

use Sidus\FilterBundle\DependencyInjection\Loader\ServiceLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SidusEAVFilterExtension extends Extension
{
    /**
     * {@inheritdoc}
     * @throws \Exception
     * @throws BadMethodCallException
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new ServiceLoader(__DIR__.'/../Resources/config/services');
        $loader->loadFiles($container);
    }
}
