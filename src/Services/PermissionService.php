<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Services;

use Callcocam\Raptor\Models\Permission;
use Illuminate\Http\Request; 

class PermissionService extends RaptorService
{
    public function __construct(Permission $model, Request $request)
    {
        parent::__construct($model, $request);
    }
}
