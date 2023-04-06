<?php

declare(strict_types=1);

namespace GeekCell\SodiumBundle\Test\Unit\Command;

use GeekCell\SodiumBundle\Command\GenerateKeysCommand;
use GeekCell\SodiumBundle\Contracts\Algorithm;
use GeekCell\SodiumBundle\Sodium\Sodium;
use GeekCell\SodiumBundle\Tests\TestKernel;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateKeysCommandTest extends TestCase
{
    public function testExecute(): void
    {
        // Given
        $kernel = new TestKernel('default');
        $kernel->boot();
        $application = new Application($kernel);

        /** @var Mockery\MockInterface&Algorithm */
        $boxMock = Mockery::mock(Algorithm::class);
        $boxMock->shouldReceive('getPublicKey')->andReturn('publicKey');
        $boxMock->shouldReceive('getPrivateKey')->andReturn('privateKey');

        /** @var Mockery\MockInterface&Sodium $sodiumMock */
        $sodiumMock = Mockery::mock(Sodium::class);
        $sodiumMock->shouldReceive('with')->with('box')->andReturn($boxMock);

        $application->add(new GenerateKeysCommand($sodiumMock));

        $command = $application->find('sodium:generate-keys');
        $commandTester = new CommandTester($command);

        // When
        $commandTester->execute([]);

        // Then
        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Public Key:  publicKey', $output);
        $this->assertStringContainsString('Private Key: privateKey', $output);
        $this->assertStringContainsString('SODIUM_PUBLIC_KEY=publicKey', $output);
        $this->assertStringContainsString('SODIUM_PRIVATE_KEY=privateKey', $output);
    }
}
