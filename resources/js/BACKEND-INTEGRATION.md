# 🔗 Integração Backend - Raptor DataTable Avançada

Como configurar o backend Laravel para funcionar perfeitamente com a tabela avançada.

## 🎯 Controller Laravel Completo

### **AbstractController.php Atualizado**

```php
<?php

namespace Callcocam\Raptor\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class AbstractController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected $model;
    protected $searchFields = ['name', 'email']; // Campos pesquisáveis
    protected $sortableFields = ['id', 'name', 'email', 'created_at']; // Campos ordenáveis
    protected $perPage = 10;

    public function index(Request $request)
    {
        $query = $this->model::query();

        // 🔍 BUSCA GLOBAL
        if ($request->has('search') && !empty($request->get('search'))) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                foreach ($this->searchFields as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        // 🎛️ FILTROS ESPECÍFICOS
        foreach ($this->getFilterOptions() as $filter) {
            $column = $filter['column'];
            if ($request->has($column) && !empty($request->get($column))) {
                $value = $request->get($column);
                
                if ($filter['type'] === 'select') {
                    $query->where($column, $value);
                } elseif ($filter['type'] === 'text') {
                    $query->where($column, 'like', "%{$value}%");
                } elseif ($filter['type'] === 'date') {
                    $query->whereDate($column, $value);
                } elseif ($filter['type'] === 'boolean') {
                    $query->where($column, (bool) $value);
                }
            }
        }

        // 📊 ORDENAÇÃO
        if ($request->has('sort') && in_array($request->get('sort'), $this->sortableFields)) {
            $column = $request->get('sort');
            $direction = $request->get('direction', 'asc');
            $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';
            
            $query->orderBy($column, $direction);
        } else {
            // Ordenação padrão
            $query->orderBy('id', 'desc');
        }

        // 📄 PAGINAÇÃO
        $perPage = $request->get('per_page', $this->perPage);
        $perPage = in_array($perPage, [10, 20, 30, 50, 100]) ? $perPage : $this->perPage;
        
        $data = $query->paginate($perPage)->withQueryString();

        return inertia($this->getIndexView(), [
            'data' => $data,
            'columns' => $this->columns(),
            'actions' => $this->actions(),
            'filterOptions' => $this->getFilterOptionsWithCounts(),
            'pageTitle' => $this->getPageTitle(),
            'pageDescription' => $this->getPageDescription(),
            'breadcrumbs' => $this->getBreadcrumbs(),
            'routeNameBase' => $this->getRouteNameBase(),
            'can' => $this->getPermissions(),
        ]);
    }

    // 🔢 FILTROS COM CONTADORES
    protected function getFilterOptionsWithCounts(): array
    {
        $filterOptions = $this->getFilterOptions();
        
        foreach ($filterOptions as &$filter) {
            if ($filter['type'] === 'select' && isset($filter['options'])) {
                foreach ($filter['options'] as &$option) {
                    // Contar registros para cada opção
                    $count = $this->model::where($filter['column'], $option['value'])->count();
                    $option['count'] = $count;
                }
            }
        }
        
        return $filterOptions;
    }

    // 🗑️ AÇÕES EM LOTE
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:' . $this->model::getTable() . ',id'
        ]);

        $ids = $request->get('ids', []);
        $count = $this->model::whereIn('id', $ids)->delete();

        return redirect()->back()->with('success', "{$count} item(s) excluído(s) com sucesso!");
    }

    public function bulkArchive(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:' . $this->model::getTable() . ',id'
        ]);

        $ids = $request->get('ids', []);
        $count = $this->model::whereIn('id', $ids)->update(['archived_at' => now()]);

        return redirect()->back()->with('success', "{$count} item(s) arquivado(s) com sucesso!");
    }

    public function export(Request $request)
    {
        $ids = explode(',', $request->get('ids', ''));
        $query = $this->model::query();

        if (!empty($ids) && $ids[0] !== '') {
            $query->whereIn('id', $ids);
        }

        $items = $query->get();
        
        // Implementar exportação (CSV, Excel, PDF)
        return $this->exportToCsv($items);
    }

    // 📋 MÉTODOS ABSTRATOS (implementar nas classes filhas)
    abstract protected function columns(): array;
    abstract protected function actions(): array;
    abstract protected function getFilterOptions(): array;
    abstract protected function getIndexView(): string;
    abstract protected function getPageTitle(): string;
    abstract protected function getPageDescription(): string;
    abstract protected function getBreadcrumbs(): array;
    abstract protected function getRouteNameBase(): string;
    abstract protected function getPermissions(): array;

    // 📤 EXPORTAÇÃO CSV
    protected function exportToCsv($items)
    {
        $filename = strtolower(class_basename($this->model)) . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($items) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // Header
            if ($items->isNotEmpty()) {
                fputcsv($file, array_keys($items->first()->toArray()));
            }
            
            // Dados
            foreach ($items as $item) {
                fputcsv($file, $item->toArray());
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
```

## 📋 Exemplo: UserController Completo

```php
<?php

namespace App\Http\Controllers\Admin;

use Callcocam\Raptor\Http\Controllers\AbstractController;
use App\Models\User;

class UserController extends AbstractController
{
    protected $model = User::class;
    protected $searchFields = ['name', 'email'];
    protected $sortableFields = ['id', 'name', 'email', 'status', 'created_at'];

    protected function columns(): array
    {
        return [
            [
                'accessorKey' => 'id',
                'header' => 'ID',
                'sortable' => true,
                'type' => 'number'
            ],
            [
                'accessorKey' => 'name',
                'header' => 'Nome',
                'sortable' => true,
                'type' => 'text'
            ],
            [
                'accessorKey' => 'email',
                'header' => 'E-mail',
                'sortable' => true,
                'type' => 'text'
            ],
            [
                'accessorKey' => 'status',
                'header' => 'Status',
                'sortable' => true,
                'formatter' => 'renderBadge',
                'options' => [
                    'active' => 'success',
                    'inactive' => 'gray',
                    'pending' => 'warning',
                    'blocked' => 'destructive'
                ]
            ],
            [
                'accessorKey' => 'email_verified_at',
                'header' => 'E-mail Verificado',
                'sortable' => true,
                'type' => 'boolean',
                'cell' => fn($value) => $value ? 'Sim' : 'Não'
            ],
            [
                'accessorKey' => 'created_at',
                'header' => 'Criado em',
                'sortable' => true,
                'type' => 'date'
            ]
        ];
    }

    protected function actions(): array
    {
        return [
            [
                'id' => 'show',
                'header' => 'Visualizar',
                'icon' => '👁️',
                'color' => 'primary',
                'routeSuffix' => 'show',
                'tooltip' => 'Visualizar usuário'
            ],
            [
                'id' => 'edit',
                'header' => 'Editar',
                'icon' => '✏️',
                'color' => 'warning',
                'routeSuffix' => 'edit',
                'tooltip' => 'Editar usuário'
            ],
            [
                'id' => 'delete',
                'header' => 'Excluir',
                'icon' => '🗑️',
                'color' => 'danger',
                'routeSuffix' => 'destroy',
                'tooltip' => 'Excluir usuário'
            ]
        ];
    }

    protected function getFilterOptions(): array
    {
        return [
            [
                'column' => 'status',
                'type' => 'select',
                'label' => 'Status',
                'options' => [
                    ['label' => 'Ativo', 'value' => 'active'],
                    ['label' => 'Inativo', 'value' => 'inactive'],
                    ['label' => 'Pendente', 'value' => 'pending'],
                    ['label' => 'Bloqueado', 'value' => 'blocked']
                    // Os contadores são adicionados automaticamente pelo método getFilterOptionsWithCounts()
                ]
            ],
            [
                'column' => 'email_verified_at',
                'type' => 'select',
                'label' => 'E-mail Verificado',
                'options' => [
                    ['label' => 'Verificado', 'value' => '1'],
                    ['label' => 'Não Verificado', 'value' => '0']
                ]
            ]
        ];
    }

    protected function getIndexView(): string
    {
        return 'admin/users/Index';
    }

    protected function getPageTitle(): string
    {
        return 'Usuários';
    }

    protected function getPageDescription(): string
    {
        return 'Gerencie os usuários do sistema com funcionalidades avançadas';
    }

    protected function getBreadcrumbs(): array
    {
        return [
            ['name' => 'Dashboard', 'href' => route('admin.dashboard')],
            ['name' => 'Usuários', 'href' => route('admin.users.index')]
        ];
    }

    protected function getRouteNameBase(): string
    {
        return 'admin.users';
    }

    protected function getPermissions(): array
    {
        return [
            'create_resource' => auth()->user()->can('create', User::class),
            'edit_resource' => auth()->user()->can('update', User::class),
            'show_resource' => auth()->user()->can('view', User::class),
            'destroy_resource' => auth()->user()->can('delete', User::class),
        ];
    }
}
```

## 🛣️ Rotas Completas

```php
<?php

// routes/web.php (ou no package)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
    
    // CRUD Padrão
    Route::resource('users', UserController::class);
    
    // 🔥 AÇÕES EM LOTE (necessárias para funcionalidades avançadas)
    Route::delete('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');
    Route::patch('users/bulk-archive', [UserController::class, 'bulkArchive'])->name('users.bulk-archive');
    Route::get('users/export', [UserController::class, 'export'])->name('users.export');
    
    // Outros recursos...
    Route::resource('roles', RoleController::class);
    Route::delete('roles/bulk-delete', [RoleController::class, 'bulkDelete'])->name('roles.bulk-delete');
    Route::patch('roles/bulk-archive', [RoleController::class, 'bulkArchive'])->name('roles.bulk-archive');
    Route::get('roles/export', [RoleController::class, 'export'])->name('roles.export');
});
```

## 🎛️ Tipos de Filtros Suportados

### **1. Select com Contadores (Botões)**
```php
[
    'column' => 'status',
    'type' => 'select',
    'label' => 'Status',
    'options' => [
        ['label' => 'Ativo', 'value' => 'active'],     // → Backend adiciona count: 15
        ['label' => 'Inativo', 'value' => 'inactive']  // → Backend adiciona count: 3
    ]
]
```

### **2. Text (Input)**
```php
[
    'column' => 'name',
    'type' => 'text',
    'label' => 'Nome'
]
```

### **3. Date (Data)**
```php
[
    'column' => 'created_at',
    'type' => 'date',
    'label' => 'Data de Criação'
]
```

### **4. Boolean (Sim/Não)**
```php
[
    'column' => 'is_active',
    'type' => 'boolean',
    'label' => 'Ativo'
]
```

## 📊 Formatação de Colunas

### **Badge com Cores**
```php
[
    'accessorKey' => 'status',
    'header' => 'Status',
    'formatter' => 'renderBadge',
    'options' => [
        'active' => 'success',      // Verde
        'inactive' => 'gray',       // Cinza
        'pending' => 'warning',     // Amarelo
        'blocked' => 'destructive'  // Vermelho
    ]
]
```

### **Boolean Colorido**
```php
[
    'accessorKey' => 'is_verified',
    'header' => 'Verificado',
    'type' => 'boolean'  // Auto-formatação
]
```

### **Data Brasileira**
```php
[
    'accessorKey' => 'created_at',
    'header' => 'Criado em',
    'type' => 'date'  // Auto-formatação
]
```

## 🚀 Testando a Integração

### **URLs de Teste:**
```
# Busca
/admin/users?search=joão

# Filtros
/admin/users?status=active&email_verified_at=1

# Ordenação
/admin/users?sort=name&direction=asc

# Combinado
/admin/users?search=admin&status=active&sort=created_at&direction=desc
```

### **Payload Ações em Lote:**
```json
// POST /admin/users/bulk-delete
{
    "ids": [1, 2, 3, 4, 5]
}

// PATCH /admin/users/bulk-archive  
{
    "ids": [1, 2, 3]
}
```

## 🔥 **NOVO: Contadores Automáticos**

O backend agora calcula automaticamente os contadores para filtros do tipo `select`:

```php
// No AbstractController, o método getFilterOptionsWithCounts() adiciona:
[
    'label' => 'Ativo',
    'value' => 'active',
    'count' => 15  // ← Calculado automaticamente
]
```

### **Frontend renderiza:**
```
Status: [Ativo (15)] [Inativo (3)] [Pendente (7)]
```

---

**🎉 Backend totalmente configurado para funcionalidades avançadas com contadores!** 