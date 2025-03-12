<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeRaptorProviderCommand extends Command
{
    protected $signature = 'raptor:provider {name : The name of the provider}';
    protected $description = 'Create a new service provider that extends AbstractRaptorServiceProvider';

    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        $name = $this->argument('name');
        
        if (!Str::endsWith($name, 'ServiceProvider')) {
            $name = $name . 'ServiceProvider';
        }

        $path = app_path('Providers/Raptor/' . $name . '.php');
        
        // Create directory if it doesn't exist
        $directory = dirname($path);
        if (!$this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        if ($this->files->exists($path)) {
            $this->error('Provider already exists!');
            return 1;
        }

        $this->files->put($path, $this->buildClass($name));
        
        $this->info('Raptor provider created successfully.');
        $this->line("<info>Provider created:</info> {$name}");
        
        return 0;
    }

    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)
                    ->replaceClass($stub, $name);
    }

    protected function getStub()
    {
        return __DIR__ . '/stubs/raptor-provider.stub';
    }

    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace(
            '{{ namespace }}', 'App\\Providers\\Raptor', $stub
        );

        return $this;
    }

    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        return str_replace('{{ class }}', $class, $stub);
    }

    protected function getNamespace($name)
    {
        return 'App\\Providers\\Raptor';
    }
}
