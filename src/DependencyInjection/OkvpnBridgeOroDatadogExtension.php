<?php

declare(strict_types=1);

namespace Okvpn\Bridge\OroDatadogBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\Kernel;

class OkvpnBridgeOroDatadogExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.yml');
        $loader->load('profiling.yml');

        // For capability with Symfony 2
        $rootDir = $container->getParameter('kernel.root_dir');
        if (Kernel::VERSION_ID > 30000) {
            //Reuse cron command to cleanup storage import directory import_export
            $container->setParameter('artifacts_path', $rootDir . '/../var/import_export');
        } else {
            $container->setParameter('artifacts_path', $rootDir . '/import_export');
        }
    }
}
