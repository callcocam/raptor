<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
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
