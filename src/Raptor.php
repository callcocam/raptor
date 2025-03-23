<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor;

use Callcocam\Raptor\Contracts\NavigationGroupInterface;
use Callcocam\Raptor\Http\Controllers\RaptorController;
use Illuminate\Support\Facades\Route;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use ReflectionClass;
use Symfony\Component\Finder\SplFileInfo;

class Raptor
{

    protected $path = 'admin';


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


    public function __construct()
    {
        $this->path = config('raptor.path', $this->path);
    }

    public static function make()
    {
        return new static();
    }

    public function path($path = null)
    {

        $this->path = $path;

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getNamespace($namespace)
    {
        return sprintf("Callcocam\Raptor\%s", $namespace);
    }


    public function generate($namespace, $name, $type = 'controller')
    {
        $namespace = $this->getNamespace($namespace);
        $class = sprintf("%s\%s", $namespace, $name);
        if (class_exists($class))
            return new $class();
        return new $namespace();
    }

    /**
     * Discover and register routes from controller classes
     *
     * @param string $controllersPath Path to controllers directory
     * @param string $prefix Route prefix
     * @param array $middleware Middleware to apply to routes
     * @param string $namespace Namespace prefix for controllers
     * @return $this
     */
    public function discoverRoutes($controllersPath = null, $prefix = '', $middleware = [], $namespace = 'App\\Http\\Controllers\\Raptor')
    {
        $controllersPath = $controllersPath ?: app_path('Http/Controllers');
        $this->filesystem = new Filesystem();

        $this->namespaceDirectories[$namespace] = $controllersPath;


        // Ensure the directory exists
        if (!file_exists($controllersPath)) {
            return $this;
        }

        // Find all controller files 
        $controllers = $this->findControllers();
        // Group routes with prefix and middleware
        Route::prefix($prefix)
            ->middleware($middleware)
            ->group(function () use ($controllers) {
                foreach ($controllers as $controller) {
                    $controllerFile = $controller;
                    $class = $controller->getControllerName();
                    // Skip if is not permission to access

                    // Skip if class does not exist
                    if (!class_exists($class)) {
                        continue;
                    }
                    Route::resource($controllerFile->getSlug(), $class);

                    // Register routes for the controller
                    Route::post(sprintf('%s/{import}/import', $controllerFile->getSlug()), [$class, 'import'])
                        ->name(sprintf('%s.import', $controllerFile->getSlug()));

                    Route::post(sprintf('%s/{export}/export', $controllerFile->getSlug()), [$class, 'export'])
                        ->name(sprintf('%s.export', $controllerFile->getSlug()));
                        
                    Route::post(sprintf('%s/{export}/export/{id}', $controllerFile->getSlug()), [$class, 'export'])
                        ->name(sprintf('%s.export.id', $controllerFile->getSlug()));
                }
            });

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

                    $controllers->push($controller);
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
}
