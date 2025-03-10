<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Services;

use App\Models\User as ModelsUser; 
use Illuminate\Http\Request;

class UserService extends RaptorService
{
    public function __construct(ModelsUser $model, Request $request)
    {
        parent::__construct($model, $request);
    }

    protected function beforeStore(array $data): void
    {
        $data['password'] = bcrypt($data['password']);
    }

    protected function beforeUpdate($model, array $data): void
    {
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
    }

    protected function afterUpdate($model, array $data): void
    {  
        if($default_address = data_get($data, 'default_address')) { 
            $model->defaultAddress()->updateOrCreate(['id' => $default_address['id'] ?? null], $default_address);
        }
 
    }
}
