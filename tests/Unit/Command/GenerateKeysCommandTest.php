<?php

declare(strict_types=1);

namespace GeekCell\SodiumBundle\Test\Unit\Command;

use GeekCell\SodiumBundle\Command\GenerateKeysCommand;
use GeekCell\SodiumBundle\Tests\TestKernel;
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
        $application->add(new GenerateKeysCommand());

        $command = $application->find('sodium:generate-keys');
        $commandTester = new CommandTester($command);

        // When
        $commandTester->execute([]);

        // Then
        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();
        $base64Regex = '[-A-Za-z0-9+=]{1,50}|=[^=]|={3,}';
        $this->assertMatchesRegularExpression("/Public Key:  {$base64Regex}/", $output);
        $this->assertMatchesRegularExpression("/Private Key: {$base64Regex}/", $output);
        $this->assertMatchesRegularExpression("/SODIUM_PUBLIC_KEY={$base64Regex}/", $output);
        $this->assertMatchesRegularExpression("/SODIUM_PRIVATE_KEY={$base64Regex}/", $output);
    }
}
