<?php


namespace Voltel\WebpackAssetsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class VoltelWebpackAssetsExtension extends Extension
{
    public const BUNDLE_ALIAS = 'voltel_webpack_assets';


    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.xml');


        // For using app parameters: https://symfony.com/doc/current/configuration/using_parameters_in_dic.html
        $configuration_rules = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration_rules, $configs);

        $definition = $container->getDefinition('voltel_webpack_assets.entrypoint_registry');
        $definition->replaceArgument(0, $container->getParameter('kernel.project_dir'));
        $definition->replaceArgument(1, $config['webpack']['stats_filepath']);
        $definition->replaceArgument(2, $config['public_dir_name']);
        $definition->replaceArgument(3, $config['webpack']['output_dir_name']);

    }

    /**
     * Read at: https://symfony.com/doc/current/configuration/using_parameters_in_dic.html
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        // custom argument to my class constructor - a string with path for current project root
        // $c_project_dir = $container->getParameter('kernel.project_dir');

        return new Configuration();
    }


    public function getAlias()
    {
        return self::BUNDLE_ALIAS;
    }

}
