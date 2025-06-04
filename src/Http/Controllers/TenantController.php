<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Http\Controllers;

use Callcocam\Raptor\Enums\TenantStatus;
use Callcocam\Raptor\Models\Tenant;
use Callcocam\Raptor\Core\Support\Column;
use Callcocam\Raptor\Core\Support\Field;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TenantController extends AbstractController
{
    protected ?string $model = Tenant::class;

    public function getSidebarMenuOrder(): int
    {
        return 10;
    }

    public function getSidebarMenuIconName(): string
    {
        return 'Building2';
    }

    public function getSidebarMenuTitle(): string
    {
        return __('Tenants');
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

            Field::make('settings', 'Configurações (JSON)')
                ->type('textarea')
                ->rows(5)
                ->placeholder('{"chave": "valor"}')
                ->colSpan(12),

            Field::make('is_primary', 'Principal')
                ->type('checkbox')
                ->colSpan(6),

            Field::make('status', 'Status')
                ->type('select')
                ->options(TenantStatus::getOptions())
                ->required()
                ->colSpan(6),
        ];
    }

    protected function getTableColumns(): array
    {
        return [
            Column::make('Nome', 'name')->sortable(),

            Column::make('Slug', 'slug')->sortable(),

            Column::make('Descrição', 'description')
                ->hideable()
                ->cell(function (Tenant $row) {
                    return $row->description ? Str::limit($row->description, 50) : '-';
                }),

            Column::make('Principal', 'is_primary')
                ->formatter('renderBadge')
                ->options([
                    true => 'success',
                    false => 'secondary',
                ])
                ->cell(function (Tenant $row) {
                    return $row->is_primary ? 'Sim' : 'Não';
                }),

            Column::make('Criado em', 'created_at')
                ->sortable()
                ->formatter('formatDate')
                ->options('dd/MM/yyyy HH:mm'),

            Column::make('Status', 'status')
                ->sortable()
                ->formatter('renderBadge')
                ->options(TenantStatus::variantOptions()),

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
                'options' => TenantStatus::options(),
            ],
            [
                'column' => 'is_primary',
                'label' => 'Principal',
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
        $tenantId = $model?->id;
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', $isUpdate ? Rule::unique('tenants')->ignore($tenantId) : Rule::unique('tenants')],
            'description' => ['nullable', 'string'],
            'settings' => ['nullable', 'json'],
            'is_primary' => ['boolean'],
            'status' => ['required', Rule::in(array_column(TenantStatus::cases(), 'value'))],
        ];
    }

    protected function getWithRelations(): array
    {
        return ['users'];
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

        // Converter settings para JSON se fornecido como string
        if (!empty($validatedData['settings']) && is_string($validatedData['settings'])) {
            $validatedData['settings'] = json_decode($validatedData['settings'], true);
        }

        $tenant = $this->model::create($validatedData);

        return redirect()->route($this->getRouteNameBase() . '.index')
            ->with('success', Str::ucfirst(Str::singular($this->getResourceName())) . ' criado(a) com sucesso.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $tenant = $this->model::findOrFail($id);
        $validatedData = $request->validate($this->getValidationRules(true, $tenant));

        // Gerar slug se não fornecido
        if (empty($validatedData['slug'])) {
            $validatedData['slug'] = Str::slug($validatedData['name']);
        } else {
            $validatedData['slug'] = Str::slug($validatedData['slug']);
        }

        // Converter settings para JSON se fornecido como string
        if (!empty($validatedData['settings']) && is_string($validatedData['settings'])) {
            $validatedData['settings'] = json_decode($validatedData['settings'], true);
        }

        $tenant->update($validatedData);

        return redirect()->route($this->getRouteNameBase() . '.index')
            ->with('success', Str::ucfirst(Str::singular($this->getResourceName())) . ' atualizado(a) com sucesso.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $tenant = $this->model::findOrFail($id);

        if ($tenant->delete()) {
            return redirect()->route($this->getRouteNameBase() . '.index')
                ->with('success', Str::ucfirst(Str::singular($this->getResourceName())) . ' excluído(a) com sucesso.');
        } else {
            return redirect()->route($this->getRouteNameBase() . '.index')
                ->with('error', 'Erro ao excluir ' . Str::singular($this->getResourceName()) . '.');
        }
    }

    protected function getInitialValuesForEdit(Model $modelInstance, array $fields = []): array
    {
        $values = parent::getInitialValuesForEdit($modelInstance, $fields);
        
        // Converter settings de array para JSON string para exibição no formulário
        if (isset($values['settings']) && is_array($values['settings'])) {
            $values['settings'] = json_encode($values['settings'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
        
        return $values;
    }
} 