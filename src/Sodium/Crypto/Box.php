<?php

declare(strict_types=1);

namespace GeekCell\SodiumBundle\Sodium\Crypto;

use GeekCell\SodiumBundle\Contracts\Algorithm;

final class Box implements Algorithm
{
    /** @var string */
    private ?string $keypair;

    /**
     * Constructor.
     *
     * @param string $publicKey        public key.
     * @param null|string $privateKey  (optional) private key.
     *
     * @throws \SodiumException
     */
    public function __construct(
        private string $publicKey,
        private ?string $privateKey = null,
    ) {
        if ($privateKey) {
            $this->keypair = \sodium_crypto_box_keypair_from_secretkey_and_publickey(
                \sodium_base642bin($privateKey, \SODIUM_BASE64_VARIANT_ORIGINAL),
                \sodium_base642bin($publicKey, \SODIUM_BASE64_VARIANT_ORIGINAL),
            );
        }
    }

    /**
     * Utility method to create a new Box instance from a randomly generated
     * pair of public and private keys.
     *
     * @return static
     *
     * @throws \SodiumException
     */
    public static function create(): static
    {
        $keypair = \sodium_crypto_box_keypair();
        $publicKey = \sodium_bin2base64(\sodium_crypto_box_publickey($keypair), \SODIUM_BASE64_VARIANT_ORIGINAL);
        $privateKey = \sodium_bin2base64(\sodium_crypto_box_secretkey($keypair), \SODIUM_BASE64_VARIANT_ORIGINAL);

        return new static($publicKey, $privateKey);
    }

    /**
     * Creates a new Box instance to encrypt messages for a recipient's public
     * key and own private key.
     *
     * @param string $publicKey  The recipient's public key.
     *
     * @return static
     *
     * @throws \LogicException
     */
    public function for(string $publicKey): static
    {
        if (!isset($this->privateKey)) {
            throw new \LogicException('Private key needed for authenticated encryption.');
        }

        return new static($publicKey, $this->getPrivateKey());
    }

    /**
     * Creates a new Box instance to decrypt messages from a sender's public
     * key and own private key.
     *
     * @param string $publicKey  The sender's public key.
     *
     * @return static
     *
     * @throws \LogicException
     */
    public function from(string $publicKey): static
    {
        if (!isset($this->privateKey)) {
            throw new \LogicException('Private key needed for authenticated decryption.');
        }

        return new static($publicKey, $this->getPrivateKey());
    }

    /**
     * Encrypt a message.
     *
     * - If no nonce is provided, the message will be encrypted using anonymous public-key encryption.
     * - The additional data parameter is not used in this algorithm.
     *
     * @param string $message
     * @param null|string $nonce
     *
     * @return string
     *
     * @throws \LogicException
     * @throws \SodiumException
     */
    public function encrypt(string $message, ?string $nonce = null, ?string $additionalData = null): string
    {
        if (null === $nonce) {
            return \sodium_bin2base64(
                \sodium_crypto_box_seal(
                    $message,
                    \sodium_base642bin($this->getPublicKey(), \SODIUM_BASE64_VARIANT_ORIGINAL),
                ),
                \SODIUM_BASE64_VARIANT_ORIGINAL,
            );
        }

        if (!isset($this->keypair)) {
            throw new \LogicException('Private key needed for authenticated encryption.');
        }

        return \sodium_bin2base64(
            \sodium_crypto_box($message, $nonce, $this->keypair),
            \SODIUM_BASE64_VARIANT_ORIGINAL,
        );
    }

    /**
     * Decrypt a cipher.
     *
     * - If no nonce is provided, the cipher will be decrypted using anonymous public-key decryption.
     * - The additional data parameter is not used in this algorithm.
     *
     * @param string $cipher
     * @param null|string $nonce
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \SodiumException
     */
    public function decrypt(string $cipher, ?string $nonce = null, ?string $additionalData = null): string
    {
        if (!isset($this->keypair)) {
            throw new \LogicException('Private key needed for decryption.');
        }

        if (null === $nonce) {
            $decrypted = \sodium_crypto_box_seal_open(
                \sodium_base642bin($cipher, \SODIUM_BASE64_VARIANT_ORIGINAL),
                $this->keypair,
            );

            if (false === $decrypted) {
                throw new \InvalidArgumentException('Invalid cipher text.');
            }

            return $decrypted;
        }

        $decrypted = \sodium_crypto_box_open(
            \sodium_base642bin($cipher, \SODIUM_BASE64_VARIANT_ORIGINAL),
            $nonce,
            $this->keypair,
        );

        if (false === $decrypted) {
            throw new \InvalidArgumentException('Invalid cipher text.');
        }

        return $decrypted;
    }

    /**
     * Get the keypair.
     *
     * @codeCoverageIgnore
     *
     * @return null|string
     */
    public function getKeypair(): ?string
    {
        if (isset($this->keypair)) {
            return $this->keypair;
        }

        return null;
    }

    /**
     * Get the public key.
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * Get the private key.
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function getPrivateKey(): ?string
    {
        if (isset($this->privateKey)) {
            return $this->privateKey;
        }

        return null;
    }
}
