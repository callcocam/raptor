<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

 namespace Callcocam\Raptor;

use Callcocam\Raptor\Facades\Raptor;
use Callcocam\Raptor\Raptor as RaptorRaptor;
use Illuminate\Support\ServiceProvider;

abstract class AbstractRaptorServiceProvider extends ServiceProvider
{
    abstract public function raptor(RaptorRaptor $raptor): RaptorRaptor;

    public function register(): void
    {
        Raptor::registerConfig(
            fn (): RaptorRaptor => $this->raptor(RaptorRaptor::make()),
        );
    }
}
