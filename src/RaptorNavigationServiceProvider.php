<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor;

use Callcocam\Raptor\Services\RaptorNavigationGenerator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider para o gerador de navegação do Raptor
 * 
 * Registra o serviço de navegação e suas diretivas Blade
 */
class RaptorNavigationServiceProvider extends ServiceProvider
{
    /**
     * Registra os serviços da aplicação
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(RaptorNavigationGenerator::class, function ($app) {
            $generator = new RaptorNavigationGenerator($app['files']);

            // Aplicar configurações do config
            if ($ttl = config('raptor.navigation.cache.ttl')) {
                $generator->setCacheTtl($ttl);
            }

            if ($namespaceDirectories = config('raptor.navigation.controller_directories')) {
                if(!isset($namespaceDirectories['Callcocam\\Raptor\\Http\\Controllers'])){
                    $namespaceDirectories['Callcocam\\Raptor\\Http\\Controllers'] = __DIR__ . '/Http/Controllers';
                }
                $generator->setNamespaceDirectories($namespaceDirectories);
            }

            return $generator;
        });

        $this->app->alias(RaptorNavigationGenerator::class, 'raptor.navigation');
    }

    /**
     * Inicializa os serviços da aplicação
     *
     * @return void
     */
    public function boot()
    {
        // Publicar configurações
        $this->publishes([
            __DIR__ . '/../config/navigation.php' => config_path('raptor/navigation.php'),
        ], 'raptor-navigation-config');

        // Registrar comandos Artisan para gerenciar navegação
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Callcocam\Raptor\Commands\GenerateNavigationCommand::class,
                \Callcocam\Raptor\Commands\ClearNavigationCacheCommand::class,
            ]);
        }

        // Registrar diretivas Blade
        $this->registerBladeDirectives();
    }

    /**
     * Registra diretivas Blade para renderização da navegação
     *
     * @return void
     */
    protected function registerBladeDirectives()
    {
        // Diretiva para renderizar a navegação completa
        Blade::directive('raptorNavigation', function () {
            return "<?php echo app('raptor.navigation')->renderHtml(); ?>";
        });

        // Diretiva para renderizar apenas itens de um grupo específico
        Blade::directive('raptorNavigationGroup', function ($expression) {
            return "<?php echo app('raptor.navigation')->renderGroupHtml({$expression}); ?>";
        });
    }
}
