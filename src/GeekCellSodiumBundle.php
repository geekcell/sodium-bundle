<?php

declare(strict_types=1);

namespace GeekCell\SodiumBundle;

use GeekCell\Facade\Facade;
use GeekCell\SodiumBundle\DependencyInjection\Compiler\AlgorithmPass;
use GeekCell\SodiumBundle\DependencyInjection\GeekCellSodiumExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GeekCellSodiumBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new GeekCellSodiumExtension();
    }

    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new AlgorithmPass());
    }

    /**
     * Hook into the boot method to enable facades.
     *
     * {@inheritDoc}
     */
    public function boot(): void
    {
        parent::boot();

        if ($this->container) {
            Facade::setContainer($this->container);
        }
    }
}
