<?php


namespace Tests\Setup\Kernel;


use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Voltel\WebpackAssetsBundle\DependencyInjection\VoltelWebpackAssetsExtension;
use Voltel\WebpackAssetsBundle\VoltelWebpackAssetsBundle;

class VoltelWebpackAssetsTestingKernel extends Kernel
{
    const FRAMEWORK_ROUTER_DEFAULT_URI = 'https://mysite.loc:80';

    public function __construct(string $c_environment = 'test', bool $l_debug = false)
    {
        parent::__construct($c_environment, $l_debug);
    }


    public function registerBundles()
    {
        return [
            new VoltelWebpackAssetsBundle(),
            new FrameworkBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) use ($loader) {

            // Services that are used in tests
            $container->loadFromExtension(VoltelWebpackAssetsExtension::BUNDLE_ALIAS, [
                //'public_dir_name' => 'public',
                'webpack' => [
                    'stats_filepath' => 'stats/stats.json',
                    //'output_dir_name' => 'dist',
                ],
            ]);

            $container->setParameter('kernel.project_dir', \realpath(__DIR__ . '/../'));

            $container->setAlias('test.voltel_webpack_assets.print_tags', 'voltel_webpack_assets.print_tags')
                ->setPublic(true);

            $container->setAlias('test.voltel_webpack_assets.entrypoint_registry', 'voltel_webpack_assets.entrypoint_registry')
                ->setPublic(true);

            // framework
            $container->loadFromExtension('framework', [
                'router' => [
                    'resource' => 'kernel::loadRoutes', //required by symfony
                    'utf8' => true, // to avoid deprecation by symfony
                    'default_uri' => self::FRAMEWORK_ROUTER_DEFAULT_URI,
                ],
            ]);

        });
    }


}//end of class
