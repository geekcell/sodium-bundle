<?php

declare(strict_types=1);

namespace GeekCell\SodiumBundle\Tests\Integration;

use GeekCell\SodiumBundle\Sodium\Crypto\Box;
use GeekCell\SodiumBundle\Sodium\Sodium;
use GeekCell\SodiumBundle\Support\Facade\Sodium as SodiumFacade;
use GeekCell\SodiumBundle\Tests\TestKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ContainerTest extends TestCase
{
    public function testConfiguration(): void
    {
        // Given
        $kernel = new TestKernel('default');
        $kernel->boot();
        $container = $kernel->getContainer();

        $expectedPublicKey = \getenv('SODIUM_PUBLIC_KEY');
        $expectedPrivateKey = \getenv('SODIUM_PRIVATE_KEY');

        // When
        $publicKey = $container->getParameter('geek_cell_sodium.public_key');
        $privateKey = $container->getParameter('geek_cell_sodium.private_key');

        // Then
        $this->assertSame($expectedPublicKey, $publicKey);
        $this->assertSame($expectedPrivateKey, $privateKey);
    }

    public function testConfigurationWithMissingPublicKey(): void
    {
        // Given
        $kernel = new TestKernel('missing_public_key');
        $this->expectException(InvalidConfigurationException::class);

        // When - Then
        $kernel->boot();
    }

    public function testServices(): void
    {
        // Given
        $kernel = new TestKernel('default');
        $kernel->boot();
        $container = $kernel->getContainer();

        $expectedPublicKey = \getenv('SODIUM_PUBLIC_KEY');
        $expectedPrivateKey = \getenv('SODIUM_PRIVATE_KEY');

        $expectedMessage = 'Hello World!';
        $box = new Box($expectedPublicKey, $expectedPrivateKey); // @phpstan-ignore-line
        $cipher = $box->encrypt($expectedMessage);

        // When
        $sodium = $container->get(Sodium::class);

        // Then
        $this->assertInstanceOf(Sodium::class, $sodium);
        $this->assertInstanceOf(Box::class, $sodium->with('box'));
        $this->assertSame($expectedPublicKey, $sodium->with('box')->getPublicKey());
        $this->assertSame($expectedPrivateKey, $sodium->with('box')->getPrivateKey());
        $this->assertSame($expectedMessage, $sodium->decrypt($cipher));
    }

    public function testFacades(): void
    {
        // Given
        $kernel = new TestKernel('default');

        // When
        $kernel->boot();

        // Then
        $this->assertInstanceOf(Sodium::class, SodiumFacade::getFacadeRoot());
        $this->assertInstanceOf(Box::class, SodiumFacade::with('box')); // @phpstan-ignore-line
    }
}
