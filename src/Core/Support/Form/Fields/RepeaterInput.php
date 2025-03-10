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

class RepeaterInput extends Field
{
    use Concerns\BelongsToOptions;

    protected string $component = 'RepeaterInput';

    protected ?string $type = 'text';

    protected array $fields = [];

    public function fields(array $fields): static
    {
        $this->fields = $fields;
        return $this;
    }

    public function toArray($model = null): array
    {
        return array_merge(parent::toArray($model), [
            'name' => $this->getName() ?? now()->format('YmdHis'),
            'fields' => $this->fields,
            'value' => $model ? data_get($model, $this->getName()) : $this->default,
        ]);
    }
}