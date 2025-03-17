<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Http\Controllers;

use App\Http\Controllers\Controller;
use Callcocam\Raptor\Contracts\NavigationGroupInterface;
use Callcocam\Raptor\Core\Support\Actions\HeaderAction;
use Callcocam\Raptor\Core\Support\Form\Form;
use Callcocam\Raptor\Core\Support\Info\Info;
use Callcocam\Raptor\Core\Support\Table\Columns\TextColumn;
use Callcocam\Raptor\Core\Support\Table\Table;
use Callcocam\Raptor\Enums\DefaultStatus;
use Callcocam\Raptor\Services\RaptorService;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/**
 * Controlador base para operações CRUD no Raptor
 * 
 * Esta classe fornece métodos padronizados para visualizações e operações de CRUD
 * Cada controlador filho deve especificar o modelo e outras configurações necessárias
 */
abstract class RaptorController extends Controller implements NavigationGroupInterface
{
    /**
     * Classe do modelo a ser utilizado pelo controlador
     * 
     * @var string|null
     */
    protected ?string $model = null;

    /**
     * Serviço do Raptor para operações adicionais
     * 
     * @var RaptorService|null
     */
    protected ?RaptorService $service = null;

    /**
     * Nome do recurso para rotas e identificação
     * 
     * @var string|null
     */
    protected ?string $resource = null;

    /**
     * Base da view para renderização das páginas
     * 
     * @var string|null
     */
    protected ?string $viewBase = 'crud';

    /**
     * Nome do campo a ser usado como identificador principal na exibição
     * 
     * @var string
     */
    protected ?string $displayName = 'name';

    /**
     * Rótulo do modelo no singular
     * 
     * Usado para identificar um registro individual nas telas de criação, edição e visualização.
     * Pode ser uma string fixa ou uma Closure que retorna uma string baseada no modelo.
     * 
     * @var string|Closure|null
     */
    protected string | Closure | null $modelLabel = null;

    /**
     * Rótulo do modelo no plural
     * 
     * Usado para identificar a coleção de registros na tela de listagem.
     * Pode ser uma string fixa ou uma Closure que retorna uma string baseada no modelo.
     * 
     * @var string|Closure|null
     */
    protected string | Closure | null $modelLabelPlural = null;

    /**
     * Descrição do modelo
     * 
     * Fornece informações adicionais sobre o modelo nas interfaces.
     * Pode ser uma string fixa ou uma Closure que retorna uma string baseada no modelo.
     * 
     * @var string|Closure|null
     */
    protected string | Closure | null $modelDescription = null;

    /**
     * Ordem de exibição na navegação
     * 
     * Define a posição do item de menu dentro ou fora de um grupo de navegação.
     * Pode ser um número, string ou Closure que retorna um número.
     * 
     * @var int|string|Closure|null
     */
    protected int | string | Closure | null $navigationSort = 0;

    /**
     * Ordem de exibição do grupo de navegação
     * 
     * Define a posição do grupo de navegação no menu principal.
     * 
     * @var int|string|Closure|null
     */
    protected int | string | Closure | null $navigationGroupSort = 0;

    /**
     * Nome do grupo de navegação
     * 
     * Agrupa itens relacionados sob um mesmo cabeçalho no menu de navegação.
     * Pode ser uma string fixa ou uma Closure que retorna uma string.
     * 
     * @var string|Closure|null
     */
    protected string | Closure | null $navigationGroup = null;

    /**
     * Ícone do item de navegação
     * 
     * Nome do ícone a ser exibido junto ao item no menu.
     * Pode ser uma string fixa ou uma Closure que retorna uma string.
     * 
     * @var string|Closure|null
     */
    protected string | Closure | null $navigationIcon = null;

    /**
     * Ícone do grupo de navegação
     * 
     * Nome do ícone a ser exibido junto ao grupo no menu.
     * Pode ser uma string fixa ou uma Closure que retorna uma string.
     * 
     * @var string|Closure|null
     */
    protected string | Closure | null $navigationGroupIcon = null;

    /**
     * Slug para uso em rotas e URLs
     * 
     * Identificador único para o modelo nas URLs.
     * Pode ser uma string fixa ou uma Closure que retorna uma string.
     * 
     * @var string|Closure|null
     */
    protected string | Closure | null $slug = null;

    /**
     * Exibe a listagem de registros
     * 
     * @param Request $request Requisição HTTP
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        return Inertia::render($this->getView('Index'), $this->table(Table::make($request))
            ->route($request->route()->getName())
            ->resource($this->resource)
            ->config([
                'modelLabel' => $this->getModelLabel(),
                'modelLabelPlural' => $this->getModelLabelPlural(),
            ])
            ->model($this->model)
            ->breadcrumb($this->getModelLabelPlural())
            ->active()
            ->headerActions([
                HeaderAction::make('Criar')
                    ->icon('Plus')
                    ->route($this->routePrefix('create'))
                    ->label('Criar'),
            ])
            ->query($this->getQuery())
            ->toArray());
    }

    /**
     * Exibe o formulário de criação de um novo registro
     * 
     * @param Request $request Requisição HTTP
     * @return \Inertia\Response
     */
    public function create(Request $request)
    {
         
        return Inertia::render($this->getView('Create'), $this->form(Form::make($request))
            ->record($this->getModel()::query()->make($this->defaults()))
            ->route($this->getUrl('store'))
            ->resource($this->resource)
            ->config([
                'modelLabel' => $this->getModelLabel(),
                'modelLabelPlural' => $this->getModelLabelPlural(),
            ])
            ->model($this->model)
            ->breadcrumb($this->getModelLabelPlural(), $this->getUrl('index'))
            ->breadcrumb(sprintf('%s %s', $this->getModelLabel(), 'Novo'))
            ->method('POST')
            ->active()
            ->toArray());
    }

    /**
     * Exibe os detalhes de um registro específico
     * 
     * @param Request $request Requisição HTTP
     * @param mixed $id Identificador do registro
     * @return \Inertia\Response
     */
    public function show(Request $request, $id)
    {
        $model = $this->getModel()::findOrFail($id);
        return Inertia::render($this->getViewShow(), $this->info(Info::make($request))
            ->route($this->routePrefix('show'), $model)
            ->resource($this->resource)
            ->config([
                'modelLabel' => sprintf('%s %s', $this->getModelLabel(), data_get($model, $this->displayName)),
                'modelLabelPlural' => $this->getModelLabelPlural(),
                'modelDescription' => $this->getModelDescription(),
            ])
            ->model($this->model)
            ->record($model)
            ->breadcrumb($this->getModelLabelPlural(), $this->getUrl('index'))
            ->breadcrumb(sprintf('%s %s', $this->getModelLabel(), data_get($model, $this->displayName)))
            ->active()
            ->toArray());
    }

    /**
     * Exibe o formulário de edição de um registro
     * 
     * @param Request $request Requisição HTTP
     * @param mixed $id Identificador do registro
     * @return \Inertia\Response
     */
    public function edit(Request $request, $id)
    {
        $model = $this->getModel()::findOrFail($id);
        return Inertia::render($this->getView('Edit'), $this->form(Form::make($request))
            ->route($this->routePrefix('update'), $model)
            ->resource($this->resource)
            ->config([
                'modelLabel' => sprintf('Editar %s %s', $this->getModelLabel(), data_get($model, $this->displayName)),
                'modelLabelPlural' => $this->getModelLabelPlural(),
                'modelDescription' => $this->getModelDescription(),
            ])
            ->model($this->model)
            ->record($model)
            ->breadcrumb($this->getModelLabelPlural(), $this->getUrl('index'))
            ->breadcrumb(sprintf('%s %s', $this->getModelLabel(), data_get($model, $this->displayName)))
            ->active()
            ->method('PUT')
            ->toArray());
    }

    /**
     * Retorna a query base para consultas ao modelo
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getQuery()
    {
        return $this->getModel()->query();
    }

    /**
     * Configura a tabela de listagem de registros
     * 
     * @param Table $table Instância da tabela
     * @return Table Tabela configurada
     */
    protected function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
            ]);
    }

    /**
     * Configura o formulário para criação/edição de registros
     * 
     * @param Form $form Instância do formulário
     * @return Form Formulário configurado
     */
    protected function form(Form $form): Form
    {
        return $form;
    }

    /**
     * Configura a exibição de informações detalhadas do registro
     * 
     * @param Info $info Instância de informações
     * @return Info Configuração de informações
     */
    protected function info(Info $info): Info
    {
        return $info;
    }

    /**
     * Gera o prefixo de rota para o recurso atual
     * 
     * @param string $route Nome da rota
     * @return string Nome completo da rota prefixado
     */
    public function routePrefix($route)
    {
        return sprintf('%s.%s', $this->getSlug(), $route);
    }

    /**
     * Gera a URL para uma determinada rota
     * 
     * @param string $url Nome da rota
     * @param Model|null $model Modelo a ser passado como parâmetro da rota
     * @param array $params Parâmetros adicionais
     * @param bool $absolute Indica se a URL deve ser absoluta
     * @return string URL gerada
     */
    public function getUrl($url, $model = null, $params = [], $absolute = false)
    {
        $routeName = $this->routePrefix($url);
        if (!Route::has($routeName)) {
            return '#';
        }
        if (!$model) {
            return route($routeName, $params, $absolute);
        }
        return route($routeName, $model, $absolute);
    }

    /**
     * Gera a URL para a listagem de registros
     * 
     * @param string $url Nome da rota
     * @param bool $absolute Indica se a URL deve ser absoluta
     * @return string URL gerada
     */
    public function getUrlIndex($url, $absolute = false)
    {
        $routeName = $this->routePrefix($url);
        if (!Route::has($routeName)) {
            return '#';
        }
        return route($routeName, null, $absolute);
    }

    /**
     * Retorna uma instância do modelo associado ao controlador
     * 
     * @return Model Instância do modelo
     */
    protected function getModel(): Model
    {
        return app($this->model);
    }

    /**
     * Obtém a base da view para renderização das páginas
     * 
     * @return string Base da view
     */
    protected function getViewBase(): string
    {
        return $this->viewBase;
    }

    /**
     * Obtém o caminho completo para uma view específica
     * 
     * @param string $view Nome da view
     * @return string Caminho completo da view
     */
    protected function getView($view = "Index")
    {
        return sprintf("%s/%s", $this->getViewBase(), $view);
    }

    /**
     * Obtém o caminho da view de detalhes
     * 
     * @return string Caminho da view de detalhes
     */
    protected function getViewShow()
    {
        return $this->getView('Show');
    }

    /**
     * Obtém o nome do recurso
     * 
     * @return string Nome do recurso
     */
    protected function getResource(): string
    {
        return $this->resource;
    }

    /**
     * Obtém o serviço do Raptor
     * 
     * @return RaptorService Serviço do Raptor
     */
    protected function getService(): RaptorService
    {
        return $this->service;
    }

    /**
     * Obtém o rótulo do modelo no singular
     * 
     * @return string Rótulo do modelo
     */
    public function getModelLabel(): string
    {
        if (is_string($this->modelLabel)) {
            return $this->modelLabel;
        }

        if (is_callable($this->modelLabel)) {
            return $this->evaluate($this->modelLabel);
        }

        return str(static::class)->afterLast('\\')->replace('Controller', '')->replace('_', ' ')->title();
    }

    /**
     * Obtém o rótulo do modelo no plural
     * 
     * @return string Rótulo plural do modelo
     */
    public function getModelLabelPlural(): string
    {
        if (is_string($this->modelLabelPlural)) {
            return $this->modelLabelPlural;
        }

        if (is_callable($this->modelLabelPlural)) {
            return $this->evaluate($this->modelLabelPlural);
        }

        return str(static::class)->afterLast('\\')->replace('Controller', '')->replace('_', ' ')->plural()->title();
    }

    /**
     * Obtém a descrição do modelo
     * 
     * @return string Descrição do modelo
     */
    public function getModelDescription(): string
    {
        if (is_string($this->modelDescription)) {
            return str($this->modelDescription)->lower()->plural()->title();
        }
        if (is_callable($this->modelDescription)) {
            return $this->evaluate($this->modelDescription);
        }
        return '';
    }

    /**
     * Obtém a ordem de exibição na navegação
     * 
     * @return int Ordem de navegação
     */
    public function getNavigationSort(): int
    {
        if (is_int($this->navigationSort)) {
            return $this->navigationSort;
        }
        if (is_string($this->navigationSort)) {
            return (int) $this->navigationSort;
        }
        if (is_callable($this->navigationSort)) {
            return $this->evaluate($this->navigationSort);
        }
        return 0;
    }

    /**
     * Obtém o nome do grupo de navegação
     * 
     * @return string|null Nome do grupo
     */
    public function getNavigationGroup(): string | null
    {
        if (is_string($this->navigationGroup)) {
            return $this->navigationGroup;
        }
        if (is_callable($this->navigationGroup)) {
            return $this->evaluate($this->navigationGroup);
        }
        return null;
    }

    /**
     * Obtém o ícone do item de navegação
     * 
     * @return string|null Nome do ícone
     */
    public function getNavigationIcon(): string | null
    {
        if (is_string($this->navigationIcon)) {
            return $this->navigationIcon;
        }
        if (is_callable($this->navigationIcon)) {
            return $this->evaluate($this->navigationIcon);
        }
        return null;
    }

    /**
     * Obtém o ícone do grupo de navegação
     * 
     * @return string|null Nome do ícone do grupo
     */
    public function getNavigationGroupIcon(): string | null
    {
        if (is_string($this->navigationGroupIcon)) {
            return $this->navigationGroupIcon;
        }
        if (is_callable($this->navigationGroupIcon)) {
            return $this->evaluate($this->navigationGroupIcon);
        }
        return 'Layers2';
    }

    /**
     * Obtém o slug para uso em rotas e URLs
     * 
     * @return string|null Slug do recurso
     */
    public function getSlug(): string | null
    {
        if (is_string($this->slug)) {
            return $this->slug;
        }
        if (is_callable($this->slug)) {
            return $this->evaluate($this->slug);
        }
        return str(static::class)->afterLast('\\')->replace('Controller', '')->replace('_', ' ')->slug()->plural();
    }

    /**
     * Obtém a ordem de exibição do grupo de navegação
     * 
     * @return int Ordem do grupo
     */
    public function getNavigationGroupSort(): int
    {
        return $this->navigationGroupSort;
    }

    /**
     * Define os valores padrão para um novo registro
     * 
     * @return array Valores padrão
     */
    protected function defaults(): array
    {
        return [
            $this->displayName => '',
            'status' => DefaultStatus::DRAFT->value,
            'description' => '',
        ];
    }

    /**
     * Define as regras de validação para criação de registros
     * 
     * @return array Regras de validação
     */
    protected function storeValidationRules(): array
    {
        return [];
    }

    /**
     * Define as regras de validação para atualização de registros
     * 
     * @param string $id Identificador do registro
     * @return array Regras de validação
     */
    protected function updateValidationRules(string $id): array
    {
        return [];
    }

    /**
     * Namo da da classe do controlador
     *
     * @return string
     */
    public function getControllerName()
    {
        return static::class;
    }

    public function isAuthorized()
    {
        if (method_exists($this, 'authorize')) {
            return $this->authorize($this->routePrefix('index'));
        } 
        return Gate::allows($this->routePrefix('index'));
    }
}
