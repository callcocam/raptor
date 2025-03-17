<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Services;

use Callcocam\Raptor\Contracts\NavigationGroupInterface;
use Callcocam\Raptor\Http\Controllers\RaptorController;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Gerador de navegação para o Raptor
 * 
 * Esta classe é responsável por escanear os controladores disponíveis,
 * extrair suas configurações de navegação e organizá-las em uma estrutura
 * hierárquica de menus e submenus.
 */
class RaptorNavigationGenerator
{
    /**
     * Tempo de duração do cache em segundos (1 hora por padrão)
     * 
     * @var int
     */
    protected int $cacheTtl = 3600;

    /**
     * Chave utilizada para armazenar a navegação no cache
     * 
     * @var string
     */
    protected string $cacheKey = 'raptor_navigation';

    /**
     * Sistema de arquivos para buscar controladores
     * 
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * Mapeamento de namespaces para diretórios
     * 
     * Array associativo onde a chave é o namespace e o valor é o diretório
     * 
     * @var array
     */
    protected array $namespaceDirectories = [];

    /**
     * Construtor
     * 
     * @param Filesystem $filesystem Sistema de arquivos
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->namespaceDirectories = config('raptor.navigation.controller_directories', [
            'Callcocam\\Raptor\\Http\\Controllers' => __DIR__ . '/../Http/Controllers',
            'App\\Http\\Controllers\\Raptor' => app_path('Http/Controllers/Raptor'),
        ]);

        if (!isset($this->namespaceDirectories['Callcocam\\Raptor\\Http\\Controllers'])) {
            $this->namespaceDirectories['Callcocam\\Raptor\\Http\\Controllers'] = __DIR__ . '/../Http/Controllers';
        }
    }

    /**
     * Gera a estrutura de navegação
     * 
     * @param bool $useCache Determina se deve usar o cache
     * @return Collection Coleção com a estrutura de navegação
     */
    public function generate(bool $useCache = false): Collection
    {
        if ($useCache && Cache::has($this->cacheKey)) {
            return Cache::get($this->cacheKey);
        }
        $controllers = $this->findControllers();
        $navigation = $this->buildNavigation($controllers);

        if ($useCache) {
            Cache::put($this->cacheKey, $navigation, now()->addSeconds($this->cacheTtl));
        }

        return $navigation;
    }

    /**
     * Limpa o cache de navegação
     * 
     * @return bool
     */
    public function clearCache(): bool
    {
        return Cache::forget($this->cacheKey);
    }

    /**
     * Define o tempo de duração do cache
     * 
     * @param int $seconds Duração em segundos
     * @return self
     */
    public function setCacheTtl(int $seconds): self
    {
        $this->cacheTtl = $seconds;
        return $this;
    }

    /**
     * Define a chave de cache
     * 
     * @param string $key Nova chave de cache
     * @return self
     */
    public function setCacheKey(string $key): self
    {
        $this->cacheKey = $key;
        return $this;
    }

    /**
     * Define o mapeamento de namespaces para diretórios
     * 
     * @param array $namespaceDirectories Array associativo [namespace => diretório]
     * @return self
     */
    public function setNamespaceDirectories(array $namespaceDirectories): self
    {
        $this->namespaceDirectories = $namespaceDirectories;
        return $this;
    }

    /**
     * Adiciona um novo mapeamento de namespace para diretório
     * 
     * @param string $namespace Namespace dos controladores
     * @param string $directory Diretório correspondente
     * @return self
     */
    public function addNamespaceDirectory(string $namespace, string $directory): self
    {
        $this->namespaceDirectories[$namespace] = $directory;
        return $this;
    }

    /**
     * Busca todos os controladores nos diretórios configurados
     * 
     * @return Collection Coleção de instâncias de controladores Raptor
     */
    protected function findControllers(): Collection
    {
        $controllers = collect();

        foreach ($this->namespaceDirectories as $namespace => $directory) {
            if (!$this->filesystem->isDirectory($directory)) {
                continue;
            }

            $files = $this->filesystem->allFiles($directory);

            foreach ($files as $file) {
                $controller = $this->getControllerFromFile($file, $namespace, $directory);
                if ($controller) {
                    if ($controller->isAuthorized()) {
                        $controllers->push($controller);
                    }
                }
            }
        }

        return $controllers;
    }

    /**
     * Extrai e instancia um controlador a partir de um arquivo
     * 
     * @param SplFileInfo $file Informações do arquivo
     * @param string $baseNamespace Namespace base para este diretório
     * @param string $baseDirectory Diretório base onde o arquivo foi encontrado
     * @return object|null Instância do controlador ou null
     */
    protected function getControllerFromFile(SplFileInfo $file, string $baseNamespace, string $baseDirectory)
    {
        // Remove a barra no final do namespace se existir
        $baseNamespace = rtrim($baseNamespace, '\\');

        // Obtém o caminho relativo do arquivo em relação ao diretório base
        $relativePath = $this->getRelativePath($file->getPathname(), $baseDirectory);

        // Constrói o namespace completo
        $namespace = $baseNamespace;
        if ($relativePath) {
            $namespace .= '\\' . str_replace('/', '\\', $relativePath);
        }

        // Nome completo da classe
        $className = $namespace . '\\' . $file->getFilenameWithoutExtension();

        try {
            // Verifica se a classe existe
            if (!class_exists($className)) {
                return null;
            }

            $reflection = new ReflectionClass($className);

            // Verifica se é um controlador Raptor válido
            if ($reflection->isAbstract() || !$reflection->isSubclassOf(RaptorController::class)) {
                return null;
            }

            // Verifica se implementa a interface de navegação
            if (!$reflection->implementsInterface(NavigationGroupInterface::class)) {
                return null;
            }

            // Instancia o controlador
            return app($className);
        } catch (\Throwable $e) {
            // Log do erro (opcional)
            // \Log::warning("Erro ao carregar controlador {$className}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtém o caminho relativo a partir do caminho base fornecido
     * 
     * @param string $path Caminho completo do arquivo
     * @param string $baseDirectory Diretório base
     * @return string Caminho relativo
     */
    protected function getRelativePath(string $path, string $baseDirectory): string
    {
        if (strpos($path, $baseDirectory) === 0) {
            // Obtém o caminho relativo do arquivo (sem o nome do arquivo)
            $relativePath = substr($path, strlen($baseDirectory) + 1);
            $lastSlashPos = strrpos($relativePath, '/');

            if ($lastSlashPos === false) {
                // O arquivo está diretamente no diretório raiz
                return '';
            }

            // Retorna apenas o diretório sem o nome do arquivo
            return substr($relativePath, 0, $lastSlashPos);
        }

        return '';
    }

    protected function getRoute($controller)
    {

        $name = $controller->routePrefix('index');
        if (Route::has($name)) {
            return [
                'name' => $name,
                'url' => route($name, [], false),
            ];
        }
        return [
            'name' => $name,
            'url' => '#',
        ];
    }

    /**
     * Constrói a estrutura de navegação a partir dos controladores
     * 
     * @param Collection $controllers Coleção de controladores
     * @return Collection Estrutura hierárquica de navegação
     */
    protected function buildNavigation(Collection $controllers): Collection
    {
        $items = collect();
        $groups = collect();

        // Primeiro passo: agrupar controladores por grupo de navegação
        foreach ($controllers as $controller) {
            $navigationGroup = $controller->getNavigationGroup();
            $route = $this->getRoute($controller);
            $item = [
                'label' => $controller->getModelLabelPlural(),
                'icon' => $controller->getNavigationIcon(),
                'slug' => $controller->getSlug(),
                'sort' => $controller->getNavigationSort(),
                'controller' => get_class($controller),
                'url' => data_get($route, 'url'),
                'active' => request()->routeIs(data_get($route, 'name')),
            ];

            if ($navigationGroup) {
                if (!$groups->has($navigationGroup)) {
                    $groups->put($navigationGroup, [
                        'label' => $navigationGroup,
                        'icon' => $controller->getNavigationGroupIcon(),
                        'sort' => $controller->getNavigationGroupSort(),
                        'items' => collect(),
                    ]);
                }

                // Adicionar o item à coleção de itens do grupo
                $groupData = $groups->get($navigationGroup);
                $groupData['items']->push($item);
                $groups->put($navigationGroup, $groupData);
            } else {
                $items->push($item);
            }
        }

        // Segundo passo: ordenar itens dentro de cada grupo
        foreach ($groups as $name => $group) {
            $sortedItems = $group['items']->sortBy('sort')->values();
            $updatedGroup = $group;
            $updatedGroup['items'] = $sortedItems;
            $groups->put($name, $updatedGroup);
        }

        // Terceiro passo: adicionar grupos ordenados à coleção final
        $sortedGroups = $groups->sortBy('sort');

        foreach ($sortedGroups as $group) {
            $items->push([
                'label' => $group['label'],
                'icon' => $group['icon'],
                'sort' => $group['sort'],
                'isGroup' => true,
                'items' => $group['items'],
            ]);
        }

        // Passo final: ordenar itens de nível superior
        return $items->sortBy('sort')->values();
    }

    /**
     * Renderiza a navegação como HTML
     * 
     * @param bool $useCache Determina se deve usar o cache
     * @return string HTML da navegação
     */
    public function renderHtml(bool $useCache = true): string
    {
        $navigation = $this->generate($useCache);
        $html = '<ul class="raptor-navigation">';

        foreach ($navigation as $item) {
            if (isset($item['isGroup']) && $item['isGroup']) {
                $html .= $this->renderGroup($item);
            } else {
                $html .= $this->renderItem($item);
            }
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * Renderiza um grupo de navegação
     * 
     * @param array $group Dados do grupo
     * @return string HTML do grupo
     */
    protected function renderGroup(array $group): string
    {
        $html = '<li class="raptor-navigation-group">';
        $html .= '<div class="raptor-navigation-group-header">';

        if (isset($group['icon'])) {
            $html .= '<span class="raptor-navigation-icon">' . $group['icon'] . '</span>';
        }

        $html .= '<span class="raptor-navigation-label">' . $group['label'] . '</span>';
        $html .= '</div>';

        $html .= '<ul class="raptor-navigation-group-items">';

        foreach ($group['items'] as $item) {
            $html .= $this->renderItem($item);
        }

        $html .= '</ul>';
        $html .= '</li>';

        return $html;
    }

    /**
     * Renderiza um item individual de navegação
     * 
     * @param array $item Dados do item
     * @return string HTML do item
     */
    protected function renderItem(array $item): string
    {
        $activeClass = isset($item['active']) && $item['active'] ? ' active' : '';

        $html = '<li class="raptor-navigation-item' . $activeClass . '">';
        $html .= '<a href="' . $item['url'] . '" class="raptor-navigation-link">';

        if (isset($item['icon'])) {
            $html .= '<span class="raptor-navigation-icon">' . $item['icon'] . '</span>';
        }

        $html .= '<span class="raptor-navigation-label">' . $item['label'] . '</span>';
        $html .= '</a>';
        $html .= '</li>';

        return $html;
    }

    /**
     * Renderiza a navegação como JSON
     * 
     * @param bool $useCache Determina se deve usar o cache
     * @return string JSON da navegação
     */
    public function renderJson(bool $useCache = true): string
    {
        $navigation = $this->generate($useCache);
        return $navigation->toJson(JSON_PRETTY_PRINT);
    }
}
