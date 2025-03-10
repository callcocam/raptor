<?php
/*
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 * 
 */

namespace Callcocam\Raptor\Support\Form\Fields;
 
use Callcocam\Raptor\Support\Form\Mask;

class PhoneInput extends Mask
{
    protected string $component = 'PhoneInput';

    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->mask('(00) 00000-0000');
    } 
}
