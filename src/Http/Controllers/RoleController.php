<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Http\Controllers;

use Callcocam\Raptor\Enums\RoleStatus;
use Callcocam\Raptor\Models\Role;
use Callcocam\Raptor\Models\Permission;
use Callcocam\Raptor\Core\Support\Column;
use Callcocam\Raptor\Core\Support\Field;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends AbstractController
{
    protected ?string $model = Role::class;

    public function getSidebarMenuOrder(): int
    {
        return 30;
    }

    public function getSidebarMenuIconName(): string
    {
        return 'Shield';
    }

    public function getSidebarMenuTitle(): string
    {
        return __('Papéis');
    }

    protected function getFields(?Model $model = null): array
    {
        $isUpdate = $model && $model->exists;

        return [
            Field::make('name', 'Nome')
                ->type('text')
                ->required()
                ->colSpan(6),

            Field::make('slug', 'Slug')
                ->type('text')
                ->required()
                ->placeholder('Será gerado automaticamente se não preenchido')
                ->colSpan(6),

            Field::make('description', 'Descrição')
                ->type('textarea')
                ->rows(3)
                ->colSpan(12),

            Field::make('special', 'Especial')
                ->type('checkbox')
                ->help('Papéis especiais têm acesso total ao sistema')
                ->colSpan(6),

            Field::make('status', 'Status')
                ->type('select')
                ->options(RoleStatus::getOptions())
                ->required()
                ->colSpan(6),

            Field::make('permissions', 'Permissões')
                ->type('checkboxList')
                ->relationship('permissions', 'name', 'id')
                ->options(Permission::pluck('name', 'id')->toArray())
                ->gridCols(3)
                ->colSpan(12),
        ];
    }

    protected function getTableColumns(): array
    {
        return [
            Column::make('Nome', 'name')->sortable(),

            Column::make('Slug', 'slug')->sortable(),

            Column::make('Descrição', 'description')
                ->hideable()
                ->cell(function (Role $row) {
                    return $row->description ? Str::limit($row->description, 50) : '-';
                }),

            Column::make('Especial', 'special')
                ->formatter('renderBadge')
                ->options([
                    true => 'warning',
                    false => 'secondary',
                ])
                ->cell(function (Role $row) {
                    return $row->special ? 'Sim' : 'Não';
                }),

            Column::make('Permissões', null)
                ->hideable()
                ->cell(function (Role $row) {
                    $count = $row->permissions->count();
                    return $count > 0 ? "{$count} permissão(ões)" : 'Nenhuma';
                }),

            Column::make('Criado em', 'created_at')
                ->sortable()
                ->formatter('formatDate')
                ->options('dd/MM/yyyy HH:mm'),

            Column::make('Status', 'status')
                ->sortable()
                ->formatter('renderBadge')
                ->options(RoleStatus::variantOptions()),

            Column::actions(),
        ];
    }

    protected function getSearchableColumns(): array
    {
        return ['name', 'slug', 'description'];
    }

    protected function getFilters(): array
    {
        return [
            [
                'column' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'options' => RoleStatus::options(),
            ],
            [
                'column' => 'special',
                'label' => 'Especial',
                'type' => 'select',
                'options' => [
                    ['value' => '1', 'label' => 'Sim'],
                    ['value' => '0', 'label' => 'Não'],
                ],
            ]
        ];
    }

    protected function getValidationRules(bool $isUpdate = false, ?Model $model = null): array
    {
        $roleId = $model?->id;
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', $isUpdate ? Rule::unique('roles')->ignore($roleId) : Rule::unique('roles')],
            'description' => ['nullable', 'string'],
            'special' => ['boolean'],
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
            'status' => ['required', Rule::in(array_column(RoleStatus::cases(), 'value'))],
        ];
    }

    protected function getWithRelations(): array
    {
        return ['permissions'];
    }

    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate($this->getValidationRules());
        $permissionIds = $validatedData['permissions'] ?? [];

        unset($validatedData['permissions']);

        // Gerar slug se não fornecido
        if (empty($validatedData['slug'])) {
            $validatedData['slug'] = Str::slug($validatedData['name']);
        } else {
            $validatedData['slug'] = Str::slug($validatedData['slug']);
        }

        $role = $this->model::create($validatedData);
        
        if ($role && $permissionIds) {
            $role->permissions()->sync($permissionIds);
        }

        return redirect()->route($this->getRouteNameBase() . '.index')
            ->with('success', Str::ucfirst(Str::singular($this->getResourceName())) . ' criado(a) com sucesso.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $role = $this->model::findOrFail($id);
        $validatedData = $request->validate($this->getValidationRules(true, $role));
        $permissionIds = $validatedData['permissions'] ?? [];

        unset($validatedData['permissions']);

        // Gerar slug se não fornecido
        if (empty($validatedData['slug'])) {
            $validatedData['slug'] = Str::slug($validatedData['name']);
        } else {
            $validatedData['slug'] = Str::slug($validatedData['slug']);
        }

        $role->update($validatedData);
        
        if ($permissionIds) {
            $role->permissions()->sync($permissionIds);
        } else {
            $role->permissions()->detach();
        }

        return redirect()->route($this->getRouteNameBase() . '.index')
            ->with('success', Str::ucfirst(Str::singular($this->getResourceName())) . ' atualizado(a) com sucesso.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $role = $this->model::findOrFail($id);

        // Verificar se o papel não está sendo usado por usuários
        if ($role->users()->count() > 0) {
            return redirect()->route($this->getRouteNameBase() . '.index')
                ->with('error', 'Não é possível excluir este papel pois ele está sendo usado por usuários.');
        }

        if ($role->delete()) {
            return redirect()->route($this->getRouteNameBase() . '.index')
                ->with('success', Str::ucfirst(Str::singular($this->getResourceName())) . ' excluído(a) com sucesso.');
        } else {
            return redirect()->route($this->getRouteNameBase() . '.index')
                ->with('error', 'Erro ao excluir ' . Str::singular($this->getResourceName()) . '.');
        }
    }
} 