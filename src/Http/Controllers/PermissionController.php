<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Http\Controllers;

use Callcocam\Raptor\Enums\PermissionStatus;
use Callcocam\Raptor\Models\Permission;
use Callcocam\Raptor\Core\Support\Column;
use Callcocam\Raptor\Core\Support\Field;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PermissionController extends AbstractController
{
    protected ?string $model = Permission::class;

    public function getSidebarMenuOrder(): int
    {
        return 40;
    }

    public function getSidebarMenuIconName(): string
    {
        return 'Key';
    }

    public function getSidebarMenuTitle(): string
    {
        return __('Permissões');
    }

    protected function getFields(?Model $model = null): array
    {
        $isUpdate = $model && $model->exists;

        return [
            Field::make('name', 'Nome')
                ->type('text')
                ->required()
                ->placeholder('Ex: users.create, posts.edit, etc.')
                ->colSpan(6),

            Field::make('slug', 'Slug')
                ->type('text')
                ->required()
                ->placeholder('Será gerado automaticamente se não preenchido')
                ->colSpan(6),

            Field::make('description', 'Descrição')
                ->type('textarea')
                ->rows(3)
                ->placeholder('Descreva o que esta permissão permite fazer')
                ->colSpan(12),

            Field::make('status', 'Status')
                ->type('select')
                ->options(PermissionStatus::getOptions())
                ->required()
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
                ->cell(function (Permission $row) {
                    return $row->description ? Str::limit($row->description, 60) : '-';
                }),

            Column::make('Papéis', null)
                ->hideable()
                ->cell(function (Permission $row) {
                    $count = $row->roles->count();
                    return $count > 0 ? "{$count} papel(éis)" : 'Nenhum';
                }),

            Column::make('Criado em', 'created_at')
                ->sortable()
                ->formatter('formatDate')
                ->options('dd/MM/yyyy HH:mm'),

            Column::make('Status', 'status')
                ->sortable()
                ->formatter('renderBadge')
                ->options(PermissionStatus::variantOptions()),

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
                'options' => PermissionStatus::options(),
            ]
        ];
    }

    protected function getValidationRules(bool $isUpdate = false, ?Model $model = null): array
    {
        $permissionId = $model?->id;
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', $isUpdate ? Rule::unique('permissions')->ignore($permissionId) : Rule::unique('permissions')],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_column(PermissionStatus::cases(), 'value'))],
        ];
    }

    protected function getWithRelations(): array
    {
        return ['roles'];
    }

    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate($this->getValidationRules());

        // Gerar slug se não fornecido
        if (empty($validatedData['slug'])) {
            $validatedData['slug'] = Str::slug($validatedData['name']);
        } else {
            $validatedData['slug'] = Str::slug($validatedData['slug']);
        }

        $permission = $this->model::create($validatedData);

        return redirect()->route($this->getRouteNameBase() . '.index')
            ->with('success', Str::ucfirst(Str::singular($this->getResourceName())) . ' criado(a) com sucesso.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $permission = $this->model::findOrFail($id);
        $validatedData = $request->validate($this->getValidationRules(true, $permission));

        // Gerar slug se não fornecido
        if (empty($validatedData['slug'])) {
            $validatedData['slug'] = Str::slug($validatedData['name']);
        } else {
            $validatedData['slug'] = Str::slug($validatedData['slug']);
        }

        $permission->update($validatedData);

        return redirect()->route($this->getRouteNameBase() . '.index')
            ->with('success', Str::ucfirst(Str::singular($this->getResourceName())) . ' atualizado(a) com sucesso.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $permission = $this->model::findOrFail($id);

        // Verificar se a permissão não está sendo usada por papéis
        if ($permission->roles()->count() > 0) {
            return redirect()->route($this->getRouteNameBase() . '.index')
                ->with('error', 'Não é possível excluir esta permissão pois ela está sendo usada por papéis.');
        }

        if ($permission->delete()) {
            return redirect()->route($this->getRouteNameBase() . '.index')
                ->with('success', Str::ucfirst(Str::singular($this->getResourceName())) . ' excluído(a) com sucesso.');
        } else {
            return redirect()->route($this->getRouteNameBase() . '.index')
                ->with('error', 'Erro ao excluir ' . Str::singular($this->getResourceName()) . '.');
        }
    }
} 