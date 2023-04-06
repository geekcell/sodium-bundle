<?php

declare(strict_types=1);

namespace GeekCell\SodiumBundle\Sodium;

use GeekCell\SodiumBundle\Contracts\Algorithm;

class Sodium implements Algorithm
{
    /** @var Algorithm[] $algos */
    private array $algos = [];

    /** @var null|string $defaultAlias  */
    private ?string $defaultAlias = null;

    /**
     * {@inheritDoc}
     * @throws \UnexpectedValueException
     */
    public function encrypt(string $message, ?string $nonce = null, ?string $additionalData = null): string
    {
        if (null === $this->defaultAlias) {
            throw new \UnexpectedValueException('No default algorithm set.');
        }

        return $this->with($this->defaultAlias)->encrypt($message, $nonce, $additionalData);
    }

    /**
     * {@inheritDoc}
     * @throws \UnexpectedValueException
     */
    public function decrypt(string $cipher, ?string $nonce = null, ?string $additionalData = null): string
    {
        if (null === $this->defaultAlias) {
            throw new \UnexpectedValueException('No default algorithm set.');
        }

        return $this->with($this->defaultAlias)->decrypt($cipher, $nonce, $additionalData);
    }

    /**
     * Add an algorithm to the list of available algorithms and optionally set it as the default.
     *
     * @param Algorithm $algo
     * @param string $alias
     * @param bool $isDefault
     *
     * @return void
     */
    public function addAlgorithm(Algorithm $algo, string $alias, bool $isDefault = false): void
    {
        $this->algos[$alias] = $algo;

        if ($isDefault) {
            $this->defaultAlias = $alias;
        }
    }

    /**
     * Get an algorithm by its alias.
     *
     * @param string $alias
     * @return Algorithm
     *
     * @throws \InvalidArgumentException
     */
    public function with(string $alias): Algorithm
    {
        if (!isset($this->algos[$alias])) {
            throw new \InvalidArgumentException(sprintf('Algorithm "%s" not found.', $alias));
        }

        return $this->algos[$alias];
    }
}
