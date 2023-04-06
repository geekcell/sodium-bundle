<?php

declare(strict_types=1);

namespace GeekCell\SodiumBundle\DependencyInjection\Compiler;

use GeekCell\SodiumBundle\Contracts\Algorithm;
use GeekCell\SodiumBundle\Sodium\Sodium;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AlgorithmPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $sodiumDefinition = $container->getDefinition(Sodium::class);
        $taggedServices = $container->findTaggedServiceIds('geek_cell_sodium.crypto.algorithm');
        foreach ($taggedServices as $id => $tags) {
            $definition = $container->getDefinition($id);
            $className = $definition->getClass();
            if (null === $className) {
                continue;
            }

            if (!is_subclass_of($className, Algorithm::class)) {
                continue;
            }

            $sodiumDefinition->addMethodCall(
                'addAlgorithm',
                [
                    $definition,
                    $tags[0]['alias'],
                    $tags[0]['is_default']
                ],
            );
        }
    }
}
