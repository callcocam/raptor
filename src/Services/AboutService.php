<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\Raptor\Services;

use Callcocam\Raptor\Models\About;
use Illuminate\Http\Request;

class AboutService extends RaptorService
{
    public function __construct(About $model, Request $request)
    {
        parent::__construct($model, $request);
    }
}
