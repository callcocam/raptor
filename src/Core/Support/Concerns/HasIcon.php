<?php
namespace Callcocam\Raptor\Core\Support\Concerns;

trait HasIcon
{
    protected ?string $icon = null;

    public function icon(string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }
}