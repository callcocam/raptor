<?php

namespace Callcocam\Raptor\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Callcocam\Raptor\Raptor
 */
class Raptor extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Callcocam\Raptor\Raptor::class;
    }
}
