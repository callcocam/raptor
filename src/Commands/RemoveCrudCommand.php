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

class RemoveCrudCommand extends Command
{
    protected $signature = 'remove:crud {name : Nome do modelo}';
    protected $description = 'Remove um CRUD completo';

    public function handle()
    {
        $name = $this->argument('name');
        $modelName = Str::studly($name);

        if (!$this->confirm("Tem certeza que deseja remover o CRUD {$modelName}?")) {
            return;
        }

        // Remover arquivos
        $files = [
            app_path("Models/{$modelName}.php"),
            app_path("Http/Controllers/Raptor/{$modelName}Controller.php"),
            app_path("Http/Resources/{$modelName}Resource.php"),
            app_path("Enums/{$modelName}Status.php"),
            database_path("factories/{$modelName}Factory.php"),
            database_path("seeders/{$modelName}Seeder.php"),
        ];

        foreach ($files as $file) {
            if (File::exists($file)) {
                if ($this->confirm("Deseja remover o arquivo {$file}?")) {
                    File::delete($file);
                    $this->info("Arquivo removido: {$file}");
                }
            }
        }

        // Remover última migration relacionada
        $migration = collect(File::files(database_path('migrations')))
            ->filter(fn($file) => Str::contains($file->getFilename(), Str::snake($name)))
            ->last();

        if ($migration) {
            if ($this->confirm("Deseja remover a última migration relacionada a {$modelName}?")) {
                File::delete($migration->getPathname());
                $this->info("Migration removida: {$migration->getFilename()}");
            }
        } else {
            $this->info("Nenhuma migration encontrada para remover");
        }

        $this->info('CRUD removido com sucesso!');
    }
}
