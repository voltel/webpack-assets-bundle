<?php


namespace Voltel\WebpackAssetsBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(VoltelWebpackAssetsExtension::BUNDLE_ALIAS);

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('public_dir_name')
                    ->info('Name of the public web-content base-folder (e.g "public")')
                    ->defaultValue('public')
                    ->end()
                //
                ->arrayNode('webpack')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('stats_filepath')
                            ->info('Filepath of the "StatsWriterPlugin" plugin output RELATIVE to the project root')
                            ->defaultValue('stats.json')
                            ->end()
                        //
                        ->scalarNode('output_dir_name')
                            ->info('Name of the Webpack build output folder (e.g. "dist" or "build")')
                            ->defaultValue('dist')
                            ->end()
                    ->end()
                ->end()// webpack
            ->end()
        ;

        return $treeBuilder;
    }

}
