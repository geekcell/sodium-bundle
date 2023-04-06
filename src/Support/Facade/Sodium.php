<?php

declare(strict_types=1);

namespace GeekCell\SodiumBundle\Support\Facade;

use GeekCell\Facade\Facade;
use GeekCell\SodiumBundle\Sodium\Sodium as SodiumService;

class Sodium extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return SodiumService::class;
    }
}
