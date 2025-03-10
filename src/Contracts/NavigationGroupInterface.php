<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Contracts;

interface NavigationGroupInterface
{
    public function getNavigationGroup(): ?string;
    public function getNavigationSort(): int;
    public function getNavigationIcon(): ?string;
    public function getNavigationGroupIcon(): ?string;
    public function getNavigationGroupSort(): int;
}
