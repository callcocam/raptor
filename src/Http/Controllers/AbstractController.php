<?php

/**
 * @package Callcocam\Raptor\Http\Controllers
 */

namespace Callcocam\Raptor\Http\Controllers;

use Callcocam\Raptor\Core\Support\Action;
use Illuminate\Routing\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Response;
use Inertia\Inertia;
use Callcocam\Raptor\Traits\ManagesSidebarMenu;
use Callcocam\Raptor\Core\Support\Column;
use Callcocam\Raptor\Core\Support\Field;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


abstract class AbstractController extends Controller
{
    use ManagesSidebarMenu, AuthorizesRequests;

    protected ?string $model = null;
    protected string $resourceName = '';
    protected string $pluralResourceName = '';
    protected string $routeNameBase = '';
    protected string $viewPrefix = 'admin/crud';

    protected array $defaultBreadcrumbs = [];
    protected string $pageTitle = '';

    public function __construct()
    {
        $this->initializeResourceNames();
        $this->middleware($this->getRouteMiddleware());
    }

    protected function initializeResourceNames(): void
    {
        if ($this->model) {
            $baseName = class_basename($this->model);
            $this->resourceName = Str::snake($baseName);
            $this->pluralResourceName = Str::plural($this->resourceName);
            $this->routeNameBase = 'admin.' . $this->pluralResourceName;
        } else {
            throw new \Exception('A propriedade $model deve ser definida no controller filho.');
        }
    }

    protected function getResourceName(): string
    {
        return __($this->resourceName);
    }

    protected function getPluralResourceName(): string
    {
        return __($this->pluralResourceName);
    }

    protected function getRouteNameBase(): string
    {
        return  $this->routeNameBase;
    }

    protected function generatePageTitle(string $action, ?Model $modelInstance = null): string
    {
        $resourceTitle = Str::ucfirst(str_replace('_', ' ', $this->getPluralResourceName()));
        $title = null;
        switch ($action) {
            case 'index':
                $title = sprintf('Gerenciar %s', $resourceTitle);
                break;
            case 'create':
                $title = sprintf('Cadastrar %s', Str::singular($resourceTitle));
                break;
            case 'edit':
                $identifier = $modelInstance ? ($modelInstance->name ?? $modelInstance->id) : '';
                $title = sprintf('Editar %s', Str::singular($resourceTitle)) . ($identifier ? ": {$identifier}" : '');
                break;
            case 'show':
                $identifier = $modelInstance ? ($modelInstance->name ?? $modelInstance->id) : '';
                $title = sprintf('Detalhes de %s', Str::singular($resourceTitle)) . ($identifier ? ": {$identifier}" : '');
                break;
            default:
                $title = $resourceTitle;
        }
        return __($title);
    }

    protected function generatePageDescription(string $action, ?Model $modelInstance = null): string
    {
        return '';
    }

    protected function generateDefaultBreadcrumbs(string $action, ?Model $modelInstance = null): array
    {
        $pluralTitle = Str::ucfirst(str_replace('_', ' ', $this->getPluralResourceName()));
        $singularTitle = Str::singular($pluralTitle);
        $indexRoute = route($this->getRouteNameBase() . '.index');

        $breadcrumbs = [
            ['title' => $pluralTitle, 'href' => $indexRoute],
        ];

        switch ($action) {
            case 'create':
                $breadcrumbs[] = ['title' => "Cadastrar Novo {$singularTitle}", 'href' => ''];
                break;
            case 'edit':
                $identifier = $modelInstance ? ($modelInstance->name ?? $modelInstance->id) : '';
                $breadcrumbs[] = ['title' => "Editar {$singularTitle}" . ($identifier ? ": {$identifier}" : ''), 'href' => ''];
                break;
            case 'show':
                $identifier = $modelInstance ? ($modelInstance->name ?? $modelInstance->id) : '';
                $breadcrumbs[] = ['title' => "Detalhes" . ($identifier ? ": {$identifier}" : ''), 'href' => ''];
                break;
        }

        return $breadcrumbs;
    }

    protected function getRouteMiddleware(): array
    {
        return ['auth'];
    }

    abstract protected function getFields(?Model $model = null): array;
    abstract protected function getTableColumns(): array;
    abstract protected function getFilters(): array;
    abstract protected function getValidationRules(bool $isUpdate = false, ?Model $model = null): array;
    abstract protected function getSearchableColumns(): array;
    protected function getImportOptions(): array
    {
        return [
            //
        ];
    }
    /**
     * Define as ações padrão para a tabela.
     * Pode ser sobrescrito por controllers filhos para lógica customizada.
     */
    protected function getTableActions(): array
    {
        $actions = [];
        if (Gate::allows($this->getSidebarMenuPermission('show'))) {
            $actions[] = Action::make('show')
                ->icon('Eye')
                ->color('success')
                ->routeNameBase($this->getRouteNameBase())
                ->routeSuffix('show')
                ->setIsHtml(false)
                ->header('Visualizar')
                ->accessorKey('show');
        }
        if (Gate::allows($this->getSidebarMenuPermission('edit'))) {
            $actions[] = Action::make('edit')
                ->icon('PenSquare')
                ->color('primary')
                ->routeNameBase($this->getRouteNameBase())
                ->routeSuffix('edit')
                ->setIsHtml(false)
                ->header('Editar')
                ->accessorKey('edit');
        }
        return $actions;
    }

    protected function getActions(): array
    {

        $actions = $this->getTableActions();

        // Adicionar ações padrão se não estiverem definidas
        return collect($actions)->map(function ($action) {
            return $action->toArray();
        })->toArray();
    }

    /**
     * Define as bulk actions (ações em massa) disponíveis na tabela.
     * Pode ser sobrescrito por controllers filhos para adicionar ações customizadas.
     *
     * @return array
     */
    protected function getBulkActions(): array
    {
        $bulkActions = [];

        // Bulk action para excluir registros selecionados
        if (Gate::allows($this->getSidebarMenuPermission('destroy'))) {
            $bulkActions[] = [
                'id' => 'BulkDelete',
                'label' => 'Excluir Selecionados',
                'icon' => 'Trash2',
                'variant' => 'destructive',
                'color' => 'danger',
                'confirmMessage' => 'Tem certeza que deseja excluir {count} registros? Esta ação não pode ser desfeita.',
                'permission' => $this->getSidebarMenuPermission('destroy'),
            ];
        }

        // Bulk action para arquivar registros (se o model tiver soft delete)
        if (method_exists($this->model, 'bootSoftDeletes')) {
            $bulkActions[] = [
                'id' => 'BulkArchive',
                'label' => 'Arquivar Selecionados',
                'icon' => 'Archive',
                'variant' => 'secondary',
                'color' => 'secondary',
                'confirmMessage' => 'Deseja arquivar {count} registros?',
            ];
        }

        // Bulk action para exportar registros selecionados
        $bulkActions[] = [
            'id' => 'BulkExport',
            'label' => 'Exportar Selecionados',
            'icon' => 'Download',
            'variant' => 'outline',
            'color' => 'primary',
        ];

        return $bulkActions;
    }
    /**
     * Define os relacionamentos que devem ser carregados (eager loaded)
     * na listagem principal (método index).
     *
     * @return array
     */
    protected function getWithRelations(): array
    {
        return []; // Padrão: não carregar nenhum relacionamento
    }

    protected function getDataToUpdate(array $validatedData, Model $modelInstance): array
    {
        return $validatedData;
    }

    /**
     * Processa os campos definidos pelo método getFields.
     * Converte objetos Field em arrays e filtra os nulos (condicionais não atendidas).
     */
    protected function processFields(?Model $model = null): array
    {
        $rawFields = $this->getFields($model);
        $processedFields = [];

        if (!empty($rawFields) && $rawFields[0] instanceof Field) {
            $processedFields = array_map(fn(Field $field) => $field->toArray(), $rawFields);
            // Filtrar campos que retornaram null (condição não atendida)
            $processedFields = array_filter($processedFields, fn($field) => $field !== null);
        } elseif (is_array($rawFields)) {
            // Se já for um array de arrays, assumir que a lógica condicional
            // já foi tratada dentro do getFields (menos ideal)
            $processedFields = $rawFields;
        }
        // Reindexar array para evitar problemas com índices faltando no JS
        return array_values($processedFields);
    }

    public function index(Request $request): Response
    {
        $this->authorize($this->getSidebarMenuPermission('index'));

        // 🔥 Capturar query params para server-side
        $perPage = $request->input('per_page', 10);
        $currentPage = $request->input('page', 1);
        $search = $request->input('search');
        $sortBy = $request->input('sort_by');
        $sortDirection = $request->input('sort_direction', 'asc');

        // 🔥 Capturar filtros ativos
        $filterDefinitions = $this->getFilters();
        $currentFilters = [];
        foreach ($filterDefinitions as $filter) {
            $filterValue = $request->input('filters.' . $filter['column']);
            if ($filterValue) {
                $currentFilters[$filter['column']] = $filterValue;
            }
        }

        // Iniciar query e carregar relacionamentos definidos
        $query = $this->model::query();
        $relationsToLoad = $this->getWithRelations();
        if (!empty($relationsToLoad)) {
            $query->with($relationsToLoad);
        }

        $rawColumns = $this->getTableColumns(); // Obter colunas (podem ser arrays ou objetos Column)

        // Processar colunas: converter objetos Column para arrays se necessário
        $tableColumns = [];
        if (!empty($rawColumns) && $rawColumns[0] instanceof Column) {
            $tableColumns = array_map(fn(Column $column) => $column->toArray(), $rawColumns);
            $tableColumns = array_filter($tableColumns, fn($column) => $column !== null);
        } elseif (is_array($rawColumns)) {
            $tableColumns = $rawColumns;
        }
        $tableColumns = array_values($tableColumns);

        // 🔥 Aplicar filtros server-side (melhorado)
        foreach ($filterDefinitions as $filter) {
            $filterValue = $request->input('filters.' . $filter['column']);
            if ($filterValue) {
                // Se o filtro for uma string com vírgulas (múltiplos valores)
                if (is_string($filterValue) && str_contains($filterValue, ',')) {
                    $values = array_map('trim', explode(',', $filterValue));
                    $query->whereIn($filter['column'], $values);
                } else {
                    // Filtro simples
                    $query->where($filter['column'], $filterValue);
                }
            }
        }

        // 🔥 Aplicar busca server-side (melhorado)
        if ($search) {
            $searchableDbColumns = $this->getSearchableColumns();

            if (!empty($searchableDbColumns)) {
                $query->where(function ($q) use ($search, $searchableDbColumns) {
                    foreach ($searchableDbColumns as $dbColumn) {
                        if (str_contains($dbColumn, '.')) {
                            // Relacionamento
                            [$relation, $relatedColumn] = explode('.', $dbColumn, 2);
                            $q->orWhereHas($relation, function ($relationQuery) use ($relatedColumn, $search) {
                                $relationQuery->where($relatedColumn, 'like', "%{$search}%");
                            });
                        } else {
                            $q->orWhere($dbColumn, 'like', "%{$search}%");
                        }
                    }
                });
            }
        }

        // 🔥 Aplicar ordenação server-side (melhorado)
        if ($sortBy && $sortDirection) {
            // Encontrar a definição da coluna solicitada
            $sortColumnDef = null;
            foreach ($tableColumns as $colDef) {
                if (($colDef['accessorKey'] ?? $colDef['id'] ?? null) === $sortBy) {
                    $sortColumnDef = $colDef;
                    break;
                }
            }

            // Verificar se a coluna existe e está marcada como sortable
            if ($sortColumnDef && ($sortColumnDef['sortable'] ?? false)) {
                $dbColumn = $sortColumnDef['accessorKey'] ?? $sortColumnDef['id'] ?? null;
                if ($dbColumn) {
                    $query->orderBy($dbColumn, $sortDirection);
                }
            }
        } else {
            // Ordenação padrão
            $defaultSortColumn = null;
            foreach ($tableColumns as $colDef) {
                if ($colDef['sortable'] ?? false) {
                    $defaultSortColumn = $colDef['accessorKey'] ?? $colDef['id'] ?? null;
                    if ($defaultSortColumn) break;
                }
            }
            if ($defaultSortColumn) {
                $query->orderBy($defaultSortColumn, 'asc');
            } else {
                $query->latest();
            }
        }

        $paginator = $query->paginate($perPage)->withQueryString();

        return Inertia::render("{$this->viewPrefix}/index", [
            // 🔥 Estrutura de dados paginados do Laravel
            'data' => [
                'data' => $paginator->items(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                ],
                'links' => [
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
            ],
            'columns' => $tableColumns,
            'filters' => $request->only(array_merge(['search', 'sort_by', 'sort_direction', 'per_page', 'page'], array_column($filterDefinitions, 'column'))),
            'filterOptions' => $filterDefinitions,

            // 🔥 Query params atuais para o frontend server-side
            'currentPage' => $currentPage,
            'perPage' => $perPage,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortDirection' => $sortDirection,
            'currentFilters' => $currentFilters,

            'pageTitle' => $this->generatePageTitle('index'),
            'pageDescription' => $this->generatePageDescription('index'),
            'breadcrumbs' => $this->generateDefaultBreadcrumbs('index'),
            'routeNameBase' => $this->getRouteNameBase(),
            'actions' => $this->getActions(),
            'bulkActions' => $this->getBulkActions(),
            'importOptions' => $this->getImportOptions(),
            'can' => [
                'create_resource' => Gate::allows($this->getSidebarMenuPermission('create')),
                'edit_resource' => Gate::allows($this->getSidebarMenuPermission('edit')),
                'show_resource' => Gate::allows($this->getSidebarMenuPermission('show')),
                'destroy_resource' => Gate::allows($this->getSidebarMenuPermission('destroy')),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize($this->getSidebarMenuPermission('create'));
        // Usar o método processFields
        $fields = $this->processFields();

        return Inertia::render("{$this->viewPrefix}/create", [
            'fields' => $fields,
            'initialValues' => new $this->model(),
            'pageTitle' => $this->generatePageTitle('create'),
            'pageDescription' => $this->generatePageDescription('create'),
            'breadcrumbs' => $this->generateDefaultBreadcrumbs('create'),
            'routeNameBase' => $this->getRouteNameBase(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize($this->getSidebarMenuPermission('store'));
        $validatedData = $request->validate($this->getValidationRules());

        // Lógica para tratar senha, se existir
        if (isset($validatedData['password'])) {
            $validatedData['password'] = bcrypt($validatedData['password']);
        }

        if (!isset($validatedData['user_id'])) {
            $validatedData['user_id'] = $request->user()->id;
        }
        $this->model::create($validatedData);

        return redirect()->route($this->getRouteNameBase() . '.index')
            ->with('success', Str::ucfirst(Str::singular($this->getResourceName())) . ' criado(a) com sucesso.');
    }

    public function show(string $id): Response
    {
        $this->authorize($this->getSidebarMenuPermission('show'));
        $modelInstance = $this->model::findOrFail($id);
        return Inertia::render("{$this->viewPrefix}/Show", [
            'model' => $modelInstance->toArray(),
            'pageTitle' => $this->generatePageTitle('show', $modelInstance),
            'pageDescription' => $this->generatePageDescription('show', $modelInstance),
            'breadcrumbs' => $this->generateDefaultBreadcrumbs('show', $modelInstance),
            'routeNameBase' => $this->getRouteNameBase(),
        ]);
    }

    public function edit(string $id): Response
    {
        $this->authorize($this->getSidebarMenuPermission('edit'));
        $modelInstance = $this->model::findOrFail($id);
        // Usar o método processFields
        $fields = $this->processFields($modelInstance);
        // Obter valores iniciais (lógica específica pode estar no controller filho)
        $initialValues = $this->getInitialValuesForEdit($modelInstance, $fields);

        // Verificar se há campos de upload


        return Inertia::render("{$this->viewPrefix}/edit", [
            'fields' => $fields,
            'initialValues' => $initialValues,
            'modelId' => $id,
            'pageTitle' => $this->generatePageTitle('edit', $modelInstance),
            'pageDescription' => $this->generatePageDescription('edit', $modelInstance),
            'breadcrumbs' => $this->generateDefaultBreadcrumbs('edit', $modelInstance),
            'routeNameBase' => $this->getRouteNameBase(),
        ]);
    }

    /**
     * Obtém os valores iniciais para o formulário de edição.
     * Pode ser sobrescrito por controllers filhos para lógica customizada.
     */
    protected function getInitialValuesForEdit(Model $modelInstance, array $fields = []): array
    {
        $values = $modelInstance->toArray();
        foreach ($fields as $field) {
            if (isset($field['relationship'])) {
                $values[$field['key']] = $this->resolveRelationship($field['relationship'], $modelInstance, $field['labelAttribute'], $field['valueAttribute']);
            }
        }
        // Remover campos desnecessários se houver (ex: avatar_url que estava no UserController)
        // unset($values['avatar_url']); 
        return $values;
    }

    public function resolveRelationship($relationship, $modelInstance, $labelAttribute = 'name', $valueAttribute = 'id'): array
    {
        $options = [];
        if ($relationship) {
            $options = $modelInstance->{$relationship}->pluck($valueAttribute)->toArray();
        }
        return $options;
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $this->authorize($this->getSidebarMenuPermission('update'));
        $modelInstance = $this->model::findOrFail($id);
        $validatedData = $this->getDataToUpdate($request->validate($this->getValidationRules(true, $modelInstance)), $modelInstance);
        // Lógica para tratar senha, se existir e não estiver vazia
        if (isset($validatedData['password']) && !empty($validatedData['password'])) {
            $validatedData['password'] = bcrypt($validatedData['password']);
        } else {
            unset($validatedData['password']); // Remover senha se vazia para não sobrescrever
        }

        $modelInstance->update($validatedData);

        return redirect()->route($this->getRouteNameBase() . '.index')
            ->with('success', Str::ucfirst(Str::singular($this->getResourceName())) . ' atualizado(a) com sucesso.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->authorize($this->getSidebarMenuPermission('destroy'));
        $modelInstance = $this->model::findOrFail($id);
        // Adicionar verificação de permissão aqui (Gate::authorize)
        $modelInstance->delete();

        return redirect()->route($this->getRouteNameBase() . '.index')
            ->with('success', Str::ucfirst(Str::singular($this->getResourceName())) . ' excluído(a) com sucesso.');
    }

    /**
     * Processa ações em massa (bulk actions) vindas do frontend.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        // Validar dados recebidos
        $validated = $request->validate([
            'action' => 'required|string',
            'selectedIds' => 'required|array|min:1',
            // 'selectedIds.*' => 'required|integer|exists:' . $this->model::getTableName() . ',id'
        ]);

        $action = $validated['action'];
        $selectedIds = $validated['selectedIds'];
        $count = count($selectedIds);

        // Log da ação para debug
        Log::info("🔥 Bulk Action executada", [
            'action' => $action,
            'selectedIds' => $selectedIds,
            'count' => $count,
            'user' => $request->user()->id ?? 'guest'
        ]);

        $actionName = sprintf('process%s', $action);

        try {
            // Processar ação baseada no ID
            if (method_exists($this, $actionName)) {
                $message = $this->$actionName($selectedIds); 
                return back()->with('success', $message); 
            } else {
                throw new \Exception("Ação bulk '{$action}' não implementada.");
            }
        } catch (\Exception $e) {
            Log::error("❌ Erro ao executar bulk action", [
                'action' => $action,
                'selectedIds' => $selectedIds,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Erro ao executar ação: ' . $e->getMessage());
        }
    }

    /**
     * Processa exclusão em massa.
     */
    protected function processBulkDelete(array $selectedIds): string
    {
        $this->authorize($this->getSidebarMenuPermission('destroy'));
        $count = count($selectedIds);
        $this->model::whereIn('id', $selectedIds)->delete();
        return "{$count} " . ($count === 1 ? 'registro excluído' : 'registros excluídos') . " com sucesso.";
    }

    /**
     * Processa arquivamento em massa (soft delete).
     */
    protected function processBulkArchive(array $selectedIds): string
    {
        $this->authorize($this->getSidebarMenuPermission('destroy'));
        $count = count($selectedIds);
        // Verificar se o model suporta soft delete
        if (!method_exists($this->model, 'bootSoftDeletes')) {
            throw new \Exception('Model não suporta soft delete para arquivamento.');
        }

        $this->model::whereIn('id', $selectedIds)->delete();
        return "{$count} " . ($count === 1 ? 'registro arquivado' : 'registros arquivados') . " com sucesso.";
    }

    /**
     * Processa exportação em massa.
     */
    protected function processBulkExport(array $selectedIds): string
    {
        // TODO: Implementar lógica de exportação
        // Por enquanto, retornar com mensagem
        $count = count($selectedIds);
        return "Exportação de {$count} registros será processada em breve.";
    }

    /**
     * Move um arquivo temporário (identificado pelo seu path no disco local)
     * para o disco de destino padrão (geralmente 'public').
     *
     * @param string $temporaryPath Caminho do arquivo no disco 'local' (ex: "tmp/uploads/xyz.jpg")
     * @param string $targetDirectory Diretório de destino no disco padrão (ex: "avatars")
     * @return string|null O caminho relativo ao disco de destino se sucesso, null caso contrário.
     */
    protected function moveTemporaryFile(string $temporaryPath, string $targetDirectory): ?string
    {
        $destinationDisk = config('filesystems.default'); // Ex: 'public' 
        // 1. Verificar se o arquivo temporário existe no disco 'local'
        if (!Storage::disk($destinationDisk)->exists($temporaryPath)) {
            Log::warning("Arquivo temporário não encontrado em [local]: " . $temporaryPath);
            return null;
        }

        // 2. Definir o caminho de destino no disco padrão (provavelmente 'public')
        $filename = basename($temporaryPath);
        // Garante que não haja barras duplicadas se $targetDirectory já terminar com uma
        $targetPath = rtrim($targetDirectory, '/') . '/' . $filename;

        // 3. Mover o arquivo do disco 'local' para o disco padrão ('public')
        try {
            // O segundo argumento do move é o caminho relativo ao disco de destino
            if (Storage::disk($destinationDisk)->move($temporaryPath, $targetPath)) {
                // O move já deleta o original se bem-sucedido
                Storage::disk($destinationDisk)->delete($temporaryPath);
                Log::info("Arquivo movido de [local] {$temporaryPath} para [{$destinationDisk}] {$targetPath}");
                return $targetPath; // Retorna o caminho relativo ao disco de destino
            } else {
                Log::error("Falha ao mover arquivo de [local] {$temporaryPath} para [{$destinationDisk}] {$targetPath}");
                // Tentar deletar o temporário mesmo em caso de falha no move?
                Storage::disk($destinationDisk)->delete($temporaryPath);
                return null;
            }
        } catch (\Exception $e) {
            Log::error("Erro ao mover arquivo {$temporaryPath}: " . $e->getMessage());
            report($e); // Reporta a exceção
            // Tentar deletar o temporário em caso de exceção?
            // Storage::disk('local')->delete($temporaryPath);
            return null;
        }
    }
}
