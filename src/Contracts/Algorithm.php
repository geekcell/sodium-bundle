<?php

declare(strict_types=1);

namespace GeekCell\SodiumBundle\Contracts;

interface Algorithm
{
    /**
     * Encrypt a message and return a base64 encoded cipher.
     *
     * @param string $message              The message to encrypt
     * @param null|string $nonce           The nonce to use
     * @param null|string $additionalData  Additional data to authenticate
     *
     * @return string
     *
     * @throws \SodiumException
     */
    public function encrypt(string $message, ?string $nonce = null, string $additionalData = null): string;

    /**
     * Decrypt a base64 encoded cipher.
     *
     * @param string $cipher               The cipher to decrypt
     * @param null|string $nonce           The nonce to use
     * @param null|string $additionalData  Additional data to authenticate
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     * @throws \SodiumException
     */
    public function decrypt(string $cipher, ?string $nonce = null, string $additionalData = null): string;
}
