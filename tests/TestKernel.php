<?php

declare(strict_types=1);

namespace GeekCell\SodiumBundle\Tests;

use GeekCell\SodiumBundle\GeekCellSodiumBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    private string $randomHash;

    public function __construct(private readonly string $configName)
    {
        parent::__construct('test', true);

        // Generate random hash
        $this->randomHash = md5(random_bytes(10));
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles(): iterable
    {
        $bundles = [];

        if (in_array($this->getEnvironment(), ['test'], true)) {
            $bundles[] = new GeekCellSodiumBundle();
        }

        return $bundles;
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/config/config_' . $this->configName . '_' . $this->getEnvironment() . '.yaml');
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir(): string
    {
        return sprintf(
            '%s/GeekCellSodiumBundle/cache/%s/%s',
            sys_get_temp_dir(),
            $this->randomHash,
            $this->getEnvironment(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/GeekCellSodiumBundle/logs';
    }
}
