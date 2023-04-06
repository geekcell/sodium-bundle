<?php

declare(strict_types=1);

namespace GeekCell\SodiumBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class GeekCellSodiumExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('geek_cell_sodium.public_key', $config['public_key']);
        $container->setParameter('geek_cell_sodium.private_key', $config['private_key']);

        $locator = new FileLocator(__DIR__ . '/../../config');
        $loader = new YamlFileLoader($container, $locator);
        $loader->load('services.yaml');
    }
}
