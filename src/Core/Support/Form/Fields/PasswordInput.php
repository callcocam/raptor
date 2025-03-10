<?php
/*
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Core\Support\Form\Fields;

use Callcocam\Raptor\Core\Support\Form\Field;

class PasswordInput extends Field
{
    protected string $component = 'PasswordInput';
    protected ?string $type = 'password';
}
