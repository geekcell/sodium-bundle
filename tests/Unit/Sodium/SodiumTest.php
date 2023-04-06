<?php

declare(strict_types=1);

namespace GeekCell\SodiumBundle\Test\Unit\Sodium;

use GeekCell\SodiumBundle\Contracts\Algorithm;
use GeekCell\SodiumBundle\Sodium\Sodium;
use Mockery;
use PHPUnit\Framework\TestCase;

class SodiumTest extends TestCase
{
    public function testEncrypt(): void
    {
        // Given
        $sodium = new Sodium();

        /** @var Mockery\MockInterface&Algorithm $algoMock */
        $algoMock = Mockery::mock(Algorithm::class);
        $algoMock
            ->shouldReceive('encrypt')
            ->with('message', null, null)
            ->andReturn('encrypted')
        ;

        $sodium->addAlgorithm($algoMock, 'algo', true);

        // When
        $result = $sodium->encrypt('message');

        // Then
        $this->assertEquals('encrypted', $result);
    }

    public function testEncryptWithoutDefault(): void
    {
        // Given
        $this->expectException(\UnexpectedValueException::class);

        // When - Then
        (new Sodium())->encrypt('message');
    }

    public function testDecrypt(): void
    {
        // Given
        $sodium = new Sodium();

        /** @var Mockery\MockInterface&Algorithm $algoMock */
        $algoMock = Mockery::mock(Algorithm::class);
        $algoMock
            ->shouldReceive('decrypt')
            ->with('cipher', null, null)
            ->andReturn('decrypted')
        ;

        $sodium->addAlgorithm($algoMock, 'algo', true);

        // When
        $result = $sodium->decrypt('cipher');

        // Then
        $this->assertEquals('decrypted', $result);
    }

    public function testDecryptWithoutDefault(): void
    {
        // Given
        $this->expectException(\UnexpectedValueException::class);

        // When - Then
        (new Sodium())->decrypt('cipher');
    }

    public function testWith(): void
    {
        // Given
        $sodium = new Sodium();

        /** @var Mockery\MockInterface&Algorithm $algoMock */
        $algoMock = Mockery::mock(Algorithm::class);
        $algoMock
            ->shouldReceive('encrypt')
            ->with('message')
            ->andReturn('encrypted')
        ;

        $sodium->addAlgorithm($algoMock, 'algo', true);

        // When
        $result = $sodium->with('algo')->encrypt('message');

        // Then
        $this->assertEquals('encrypted', $result);
    }

    public function testWithWithoutMatchingAlias(): void
    {
        // Given
        $sodium = new Sodium();

        /** @var Mockery\MockInterface&Algorithm $algoMock */
        $algoMock = Mockery::mock(Algorithm::class);
        $algoMock->shouldReceive('encrypt')->never();

        $sodium->addAlgorithm($algoMock, 'algo', true);

        $this->expectException(\InvalidArgumentException::class);

        // When - Then
        $sodium->with('no-match')->encrypt('message');
    }
}
