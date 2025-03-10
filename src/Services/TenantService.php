<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Services;

use Callcocam\Raptor\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantService extends RaptorService
{
    public function __construct(Tenant $model, Request $request)
    {
        parent::__construct($model, $request);
    }

    public function create(array $data)
    {
        if (!empty($data['is_primary'])) {
            $this->unsetOtherPrimaryDomains();
        }

        $data['slug'] = Str::slug($data['name']);
        if (empty($data['prefix'])) {
            $data['prefix'] = Str::slug($data['name']);
        }

        $tenant = parent::create($data);

        if ($tenant) {
            $this->createDatabase($tenant);
            $this->createDefaultPermissions($tenant);
        }

        return $tenant;
    }

    protected function unsetOtherPrimaryDomains()
    {
        $this->model->where('is_primary', true)->update(['is_primary' => false]);
    }

    public function createDatabase(Tenant $tenant)
    {
        // Lógica para criar banco de dados do tenant
        // Implementar conforme necessidade
    }

    protected function createDefaultPermissions(Tenant $tenant)
    {
        $permissions = [
            ['name' => 'Visualizar Dashboard', 'slug' => 'dashboard.index'],
            ['name' => 'Gerenciar Usuários', 'slug' => 'users.manage'],
            ['name' => 'Gerenciar Funções', 'slug' => 'roles.manage'],
            ['name' => 'Gerenciar Permissões', 'slug' => 'permissions.manage'],
        ];

        foreach ($permissions as $permission) {
            if (!$tenant->permissions()->where('slug', $permission['slug'])->exists()) {
                $tenant->permissions()->create($permission);
            }
        }
    }
}
