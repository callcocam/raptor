<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Http\Controllers;

use App\Http\Controllers\Controller;
use Callcocam\Raptor\Contracts\NavigationGroupInterface;
use Callcocam\Raptor\Enums\DefaultStatus;
use Callcocam\Raptor\Services\RaptorService;
use Callcocam\Raptor\Support\Actions\HeaderAction;
use Callcocam\Raptor\Support\Form\Form;
use Callcocam\Raptor\Support\Info\Info;
use Callcocam\Raptor\Support\Table\Columns\TextColumn;
use Callcocam\Raptor\Support\Table\Table;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

abstract class RaptorController extends Controller implements NavigationGroupInterface
{
    protected ?string $model = null;

    protected ?RaptorService $service = null;

    protected ?string $resource = null;

    protected ?string $viewBase = 'crud';

    protected ?string $displayName = 'name';

    /**
     * @var string|Closure|null
     * 
     * Uma string ou uma closure que retorna uma string que será usada para renderizar o componente Inertia. 
     * Se for uma closure, ela receberá o modelo como argumento.
     * Se for uma string, ela será usada diretamente.
     * Se for nula, o componente Inertia será renderizado com o nome da classe do controlador.
     * Serve para identificar o mdelo no singular que será exibido na tela do crud de um registro create, edit, show
     */
    protected string | Closure | null $modelLabel = null;

    /**
     * @var string|Closure|null
     * 
     * Uma string ou uma closure que retorna uma string que será usada para renderizar o componente Inertia.
     * Se for uma closure, ela receberá o modelo como argumento.
     * Se for uma string, ela será usada diretamente.
     * Se for nula, o componente Inertia será renderizado com o nome da classe do controlador.
     * Serve para identificar o mdelo no plural que será exibido na tela do crud de um registro index
     */
    protected string | Closure | null $modelLabelPlural = null;


    /**
     * @var string|Closure|null
     * 
     * Uma string ou uma closure que retorna uma string que será usada para renderizar o componente Inertia.
     * Se for uma closure, ela receberá o modelo como argumento.
     * Se for uma string, ela será usada diretamente.
     * Se for nula, o componente Inertia será renderizado com o nome da classe do controlador.
     * Serve para identificar o mdelo no plural que será exibido na tela do crud de um registro index
     */
    protected string | Closure | null $modelDescription = null;

    /**
     * @var int|string|Closure|null
     * 
     * Usado para definir a ordem de exibição da navegação. dentro ou fora de um grupo de navegação.
     * Se for um número, será usado diretamente.
     * Se for uma string, será convertido em um número.
     * Se for uma closure, ela receberá o modelo como argumento.
     * 
     */
    protected int | string | Closure | null $navigationSort = 0;

    protected int | string | Closure | null $navigationGroupSort = 0;

    /**
     * @var string|Closure|null
     * 
     * Uma string ou uma closure que retorna uma string que será usada para agrupar a navegação.
     * Se for uma closure, ela receberá o modelo como argumento.
     * Se for uma string, ela será usada diretamente.
     * 
     */
    protected string | Closure | null $navigationGroup = null;

    /**
     * @var string|Closure|null
     * 
     * Uma string ou uma closure que retorna uma string que será usada para mostrar o ícone da navegação.
     * Se for uma closure, ela receberá o modelo como argumento.
     * Se for uma string, ela será usada diretamente.
     * 
     */
    protected string | Closure | null $navigationIcon = null;

    protected string | Closure | null $navigationGroupIcon = null;

    /**
     * @var string|Closure|null
     * 
     * Uma string ou uma closure que retorna uma string que será usada para gerar o slug da navegação.
     * Se for uma closure, ela receberá o modelo como argumento.
     * Se for uma string, ela será usada diretamente.
     * 
     */
    protected string | Closure | null $slug = null;

    public function index(Request $request)
    {
        return Inertia::render($this->getView('Index'),  $this->table(Table::make($request))
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

    public function create(Request $request)
    {
        return Inertia::render($this->getView('Create'),  $this->form(Form::make($request))
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



    public function show(Request $request, $id)
    {
        $model = $this->getModel()::findOrFail($id);
        return Inertia::render($this->getViewShow(),  $this->info(Info::make($request))
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

    public function edit(Request $request, $id)
    {
        $model = $this->getModel()::findOrFail($id);
        return Inertia::render($this->getView('Edit'),  $this->form(Form::make($request))
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


    protected function getQuery()
    {
        return $this->getModel()->query();
    }

    protected function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
            ]);
    }

    protected function form(Form $form): Form
    {
        return $form;
    }


    protected function info(Info $info): Info
    {
        return $info;
    }

    /**
     * @param string $route
     * 
     * @return string
     * 
     */
    public function routePrefix($route)
    {
        return  sprintf('%s.%s', $this->getSlug(), $route);
    }

    /**
     * @param string $url
     * @param Model|null $model
     * @param array $params
     * @param bool $absolute
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
     * @param string $url
     * @param Model|null $model
     * @param array $params
     * @param bool $absolute
     */
    public function getUrlIndex($url,  $absolute = false)
    {
        $routeName = $this->routePrefix($url);
        if (!Route::has($routeName)) {
            return '#';
        }
        return route($routeName, null, $absolute);
    }

    /**
     * @return Model
     */
    protected function getModel(): Model
    {
        return app($this->model);
    }

    /**
     * @return string
     */
    protected function getViewBase(): string
    {
        return $this->viewBase;
    }

    protected function getView($view = "Index")
    {
        return sprintf("%s/%s", $this->getViewBase(),   $view);
    }

    protected function getViewShow()
    {
        return  $this->getView('Show');
    }

    protected function getResource(): string
    {
        return $this->resource;
    }

    protected function getService(): RaptorService
    {
        return $this->service;
    }

    public function getModelLabel(): string
    {
        if (is_string($this->modelLabel)) {
            return $this->modelLabel;
        }

        if (is_callable($this->modelLabel)) {
            return $this->evaluate($this->modelLabel);
        }

        return  str(static::class)->afterLast('\\')->replace('Controller', '')->replace('_', ' ')->title();
    }

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


    public function getNavigationGroupSort(): int
    {
        return $this->navigationGroupSort;
    }

    protected function defaults(): array
    {
        return [
            $this->displayName => '',
            'status' => DefaultStatus::DRAFT->value,
            'description' => '',
        ];
    }

    protected function storeValidationRules(): array
    {
        return [];
    }

    protected function updateValidationRules(string $id): array
    {
        return [];
    }
}
