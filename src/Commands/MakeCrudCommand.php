<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeCrudCommand extends Command
{
    protected $signature = 'make:crud {name : Nome do modelo}';
    protected $description = 'Cria um CRUD completo';

    public function handle()
    {
        $name = $this->argument('name');
        $modelName = Str::studly($name);
        $tableName = Str::plural(Str::snake($name));

        // Criar diretórios necessários
        $this->createDirectories();

        // Criar arquivos
        $this->createEnum($modelName);
        $this->createMigration($tableName);
        $this->createModel($modelName);

        // Criar Service e Requests
        $this->createService($modelName);
        $this->createStoreRequest($modelName);
        $this->createUpdateRequest($modelName);

        $this->createController($modelName);
        $this->createFactory($modelName);
        $this->createSeeder($modelName);
        $this->createResource($modelName);


        $this->info('CRUD criado com sucesso!');
    }

    protected function createDirectories()
    {
        $directories = [
            app_path('Enums'),
            app_path('Models'),
            app_path('Http/Controllers/Raptor'),
            app_path('Http/Resources'),
            app_path('Http/Requests'),
            app_path('Services'),
            database_path('factories'),
            database_path('seeders'),
        ];

        foreach ($directories as $directory) {
            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
        }
    }

    protected function createFile($path, $content, $type = 'arquivo')
    {
        // Make sure the directory exists
        $directory = dirname($path);
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        if (File::exists($path)) {
            if (!$this->confirm("O {$type} já existe. Deseja sobrescrever?", false)) {
                $this->info("{$type} mantido sem alterações.");
                return false;
            }
        }

        File::put($path, $content);
        $this->info("{$type} criado com sucesso.");
        return true;
    }

    protected function createEnum($name)
    {
        $stub = File::get(__DIR__ . '/stubs/enum.stub');
        $content = str_replace('{{name}}', $name, $stub);

        return $this->createFile(
            app_path("Enums/{$name}Status.php"),
            $content,
            'Enum'
        );
    }

    protected function createMigration($tableName)
    {
        $stub = File::get(__DIR__ . '/stubs/migration.stub');
        $content = str_replace(
            ['{{table}}', '{{name}}'],
            [$tableName, $this->argument('name')],
            $stub
        );

        $filename = date('Y_m_d_His') . "_create_{$tableName}_table.php";

        $migration = collect(File::files(database_path('migrations')))
            ->filter(fn($file) => Str::contains($file->getFilename(), Str::snake($tableName)))
            ->last();

        if ($migration) {
            if ($this->confirm("A migration já existe. Deseja sobrescrever?", false)) {
                return $this->createFile(
                    database_path("migrations/{$filename}"),
                    $content,
                    'Migration'
                );
            } else {
                $this->info("Migration mantida sem alterações.");
                return false;
            }
        }

        return $this->createFile(
            database_path("migrations/{$filename}"),
            $content,
            'Migration'
        );
    }

    protected function createModel($name)
    {
        $stub = File::get(__DIR__ . '/stubs/model.stub');
        $content = str_replace('{{name}}', $name, $stub);

        return $this->createFile(
            app_path("Models/{$name}.php"),
            $content,
            'Model'
        );
    }

    protected function createController($name)
    {
        $stub = File::get(__DIR__ . '/stubs/controller.stub');
        $routePrefix = Str::plural(Str::snake($name));
        $content = str_replace(
            ['{{name}}', '{{routePrefix}}'],
            [$name, $routePrefix],
            $stub
        );

        return $this->createFile(
            app_path("Http/Controllers/Raptor/{$name}Controller.php"),
            $content,
            'Controller'
        );
    }

    protected function createResource($name)
    {
        $stub = File::get(__DIR__ . '/stubs/resource.stub');
        $content = str_replace('{{name}}', $name, $stub);

        return $this->createFile(
            app_path("Http/Resources/{$name}Resource.php"),
            $content,
            'Resource'
        );
    }

    protected function createFactory($name)
    {
        $stub = File::get(__DIR__ . '/stubs/factory.stub');
        $content = str_replace('{{name}}', $name, $stub);

        return $this->createFile(
            database_path("factories/{$name}Factory.php"),
            $content,
            'Factory'
        );
    }

    protected function createSeeder($name)
    {
        $stub = File::get(__DIR__ . '/stubs/seeder.stub');
        $content = str_replace('{{name}}', $name, $stub);

        return $this->createFile(
            database_path("seeders/{$name}Seeder.php"),
            $content,
            'Seeder'
        );
    }

    protected function createService($name)
    {
        $stub = File::get(__DIR__ . '/stubs/service.stub');
        $content = str_replace('{{name}}', $name, $stub);

        return $this->createFile(
            app_path("Services/{$name}Service.php"),
            $content,
            'Service'
        );
    }

    protected function createStoreRequest($name)
    {
        $stub = File::get(__DIR__ . '/stubs/store-request.stub');
        $content = str_replace('{{name}}', $name, $stub);
        $requestDirectory = app_path("Http/Requests/{$name}");

        // Ensure the model-specific request directory exists
        if (!File::isDirectory($requestDirectory)) {
            File::makeDirectory($requestDirectory, 0755, true);
        }

        return $this->createFile(
            "{$requestDirectory}/StoreRequest.php",
            $content,
            'Store Request'
        );
    }

    protected function createUpdateRequest($name)
    {
        $stub = File::get(__DIR__ . '/stubs/update-request.stub');
        $content = str_replace('{{name}}', $name, $stub);
        $requestDirectory = app_path("Http/Requests/{$name}");

        // Ensure the model-specific request directory exists
        if (!File::isDirectory($requestDirectory)) {
            File::makeDirectory($requestDirectory, 0755, true);
        }

        return $this->createFile(
            "{$requestDirectory}/UpdateRequest.php",
            $content,
            'Update Request'
        );
    }
}
