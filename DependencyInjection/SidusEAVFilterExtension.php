<?php

namespace Sidus\EAVFilterBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;

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
        $configuration = new Configuration('sidus_eav_filter');
        $config = $this->processConfiguration($configuration, $configs);

        // Automatically declare a service for each attribute configured
        foreach ($config['configurations'] as $code => $configuration) {
            $this->addConfigurationServiceDefinition($code, $configuration, $container);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/services'));
        $loader->load('configuration.yml');
        $loader->load('filter_types.yml');
    }

    /**
     * @param string           $code
     * @param array            $configuration
     * @param ContainerBuilder $container
     *
     * @throws BadMethodCallException
     */
    protected function addConfigurationServiceDefinition($code, array $configuration, ContainerBuilder $container)
    {
        $definition = new Definition(
            new Parameter('sidus_eav_filter.configuration.class'),
            [
                $code,
                new Reference('doctrine'),
                new Reference('sidus_filter.filter.factory'),
                $configuration,
                new Reference('sidus_eav_model.family_configuration.handler'),
            ]
        );
        $definition->setPublic(false);
        $container->setDefinition('sidus_eav_filter.configuration.'.$code, $definition);
    }
}
