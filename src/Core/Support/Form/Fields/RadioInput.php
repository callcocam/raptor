<?php
/*
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 * 
 * */

namespace Callcocam\Raptor\Support\Form\Fields;

use Callcocam\Raptor\Support\Form\Field;
use Callcocam\Raptor\Support\Concerns;

class RadioInput extends Field
{
    use Concerns\BelongsToOptions;

    protected string $component = 'RadioInput';
    protected ?string $type = 'radio';
    protected bool $inline = false;

    public function inline(bool $inline = true): static
    {
        $this->inline = $inline;
        return $this;
    }

    public function toArray($model = null): array
    {
        return array_merge(parent::toArray($model), [
            'options' => $this->getOptions(),
            'inline' => $this->inline,
            'value' => $model ? data_get($model, $this->getName()) : $this->default,
        ]);
    }
}
