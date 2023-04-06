<?php

declare(strict_types=1);

namespace GeekCell\SodiumBundle\Sodium\Crypto;

use PHPUnit\Framework\TestCase;

class BoxTest extends TestCase
{
    /** @var string */
    private const MESSAGE = 'This is a test message';

    public function testCreate(): void
    {
        // Given - When
        $box = Box::create();

        // Then
        $this->assertInstanceOf(Box::class, $box);
        $this->assertNotNull($box->getPublicKey());
        $this->assertNotNull($box->getPrivateKey());
    }

    public function testGetPublicKey(): void
    {
        // Given
        $keypair = \sodium_crypto_box_keypair();
        $publicKey = \sodium_bin2base64(\sodium_crypto_box_publickey($keypair), \SODIUM_BASE64_VARIANT_ORIGINAL);
        $privateKey = \sodium_bin2base64(\sodium_crypto_box_secretkey($keypair), \SODIUM_BASE64_VARIANT_ORIGINAL);

        $box = new Box($publicKey, $privateKey);
        $keypair = $box->getKeypair();

        if (null === $keypair) {
            $this->fail('Keypair is null');
        }

        $publicKey = \sodium_bin2base64(
            \sodium_crypto_box_publickey($keypair),
            \SODIUM_BASE64_VARIANT_ORIGINAL
        );

        // When
        $result = $box->getPublicKey();

        // Then
        $this->assertEquals($publicKey, $result);
    }

    public function testGetPrivateKey(): void
    {
        // Given
        $keypair = \sodium_crypto_box_keypair();
        $publicKey = \sodium_bin2base64(\sodium_crypto_box_publickey($keypair), \SODIUM_BASE64_VARIANT_ORIGINAL);
        $privateKey = \sodium_bin2base64(\sodium_crypto_box_secretkey($keypair), \SODIUM_BASE64_VARIANT_ORIGINAL);

        $box = new Box($publicKey, $privateKey);
        $keypair = $box->getKeypair();

        if (null === $keypair) {
            $this->fail('Keypair is null');
        }

        $privateKey = \sodium_bin2base64(
            \sodium_crypto_box_secretkey($keypair),
            \SODIUM_BASE64_VARIANT_ORIGINAL
        );

        // When
        $result = $box->getPrivateKey();

        // Then
        $this->assertEquals($privateKey, $result);
    }

    public function testAnonymousEncryptDecrypt(): void
    {
        // Given
        $keypair = \sodium_crypto_box_keypair();
        $publicKey = \sodium_bin2base64(\sodium_crypto_box_publickey($keypair), \SODIUM_BASE64_VARIANT_ORIGINAL);
        $privateKey = \sodium_bin2base64(\sodium_crypto_box_secretkey($keypair), \SODIUM_BASE64_VARIANT_ORIGINAL);

        $box = new Box($publicKey, $privateKey);
        $message = self::MESSAGE;

        // When
        $encrypted = $box->encrypt($message);
        $decrypted = $box->decrypt($encrypted);

        // Then
        $this->assertNotEquals($message, $encrypted);
        $this->assertEquals($message, $decrypted);
    }

    public function testAnonymousEncryptWithoutPrivateKey(): void
    {
        // Given
        $keypair = \sodium_crypto_box_keypair();
        $publicKey = \sodium_bin2base64(\sodium_crypto_box_publickey($keypair), \SODIUM_BASE64_VARIANT_ORIGINAL);

        $box = new Box($publicKey);
        $message = self::MESSAGE;

        // When
        $encrypted = $box->encrypt($message);

        // Then
        $this->assertNotEquals($message, $encrypted);
    }

    public function testAnonymousDecryptWithoutPrivateKey(): void
    {
        // Given
        $keypair = \sodium_crypto_box_keypair();
        $publicKey = \sodium_bin2base64(\sodium_crypto_box_publickey($keypair), \SODIUM_BASE64_VARIANT_ORIGINAL);

        $box = new Box($publicKey);

        $this->expectException(\LogicException::class);

        // When - Then
        $box->decrypt('does-not-matter');
    }

    public function testAuthenticatedEncryptDecrypt(): void
    {
        // Given
        $senderKeypair = \sodium_crypto_box_keypair();
        $senderPublicKey = \sodium_bin2base64(
            \sodium_crypto_box_publickey($senderKeypair),
            \SODIUM_BASE64_VARIANT_ORIGINAL
        );
        $senderPrivateKey = \sodium_bin2base64(
            \sodium_crypto_box_secretkey($senderKeypair),
            \SODIUM_BASE64_VARIANT_ORIGINAL
        );

        $recipientKeypair = \sodium_crypto_box_keypair();
        $recipientPublicKey = \sodium_bin2base64(
            \sodium_crypto_box_publickey($recipientKeypair),
            \SODIUM_BASE64_VARIANT_ORIGINAL
        );
        $recipientPrivateKey = \sodium_bin2base64(
            \sodium_crypto_box_secretkey($recipientKeypair),
            \SODIUM_BASE64_VARIANT_ORIGINAL
        );

        $sender = new Box($recipientPublicKey, $senderPrivateKey);
        $recipient = new Box($senderPublicKey, $recipientPrivateKey);
        $nonce = \random_bytes(\SODIUM_CRYPTO_BOX_NONCEBYTES);

        // When
        $cipher = $sender->for($recipientPublicKey)->encrypt(self::MESSAGE, $nonce);
        $message = $recipient->from($senderPublicKey)->decrypt($cipher, $nonce);

        // Then
        $this->assertNotEquals(self::MESSAGE, $cipher);
        $this->assertEquals(self::MESSAGE, $message);
    }

    public function testAuthenticatedEncryptWithoutPrivateKey(): void
    {
        // Given
        $senderKeypair = \sodium_crypto_box_keypair();
        $senderPublicKey = \sodium_bin2base64(
            \sodium_crypto_box_publickey($senderKeypair),
            \SODIUM_BASE64_VARIANT_ORIGINAL
        );

        $sender = new Box($senderPublicKey, null);
        $nonce = \random_bytes(\SODIUM_CRYPTO_BOX_NONCEBYTES);

        $this->expectException(\LogicException::class);

        // When - Then
        $sender->for('does-not-matter')->encrypt(self::MESSAGE, $nonce);
    }

    public function testAuthenticatedDecryptWithoutPrivateKey(): void
    {
        // Given
        $recipientKeypair = \sodium_crypto_box_keypair();
        $recipientPublicKey = \sodium_bin2base64(
            \sodium_crypto_box_publickey($recipientKeypair),
            \SODIUM_BASE64_VARIANT_ORIGINAL
        );

        $recipient = new Box($recipientPublicKey, null);
        $nonce = \random_bytes(\SODIUM_CRYPTO_BOX_NONCEBYTES);

        $this->expectException(\LogicException::class);

        // When - Then
        $recipient->from('does-not-matter')->decrypt('does-not-matter', $nonce);
    }
}
