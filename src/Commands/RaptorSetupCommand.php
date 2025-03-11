<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Commands;

use App\Models\User;
use Callcocam\Raptor\Enums\DefaultStatus;
use Callcocam\Raptor\Enums\PermissionStatus;
use Callcocam\Raptor\Models\Permission;
use Callcocam\Raptor\Models\Role;
use Callcocam\Raptor\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class RaptorSetupCommand extends Command
{
    public $signature = 'raptor:setup';

    public $description = 'Create some initial resources for Raptor, such as roles, permissions, and a user.';

    public function handle(): int
    {
        $this->comment('All done');

        if ($this->confirm('Do you want to create users?')) {
            $user = $this->createUsers();
        }

        if ($this->confirm('Do you want to create roles?')) {
            $role  = $this->ask('What role do you want to create?');
            $isAdministrator = $this->confirm('Is this role an administrator?');
            if ($isAdministrator) {
                $this->createRole($role, $user,  'all-access');
            } else {
                $this->createRole($role, $user);
            }
        }


        return self::SUCCESS;
    }

    protected function createTenant()
    {
        $this->comment('Creating tenant');
        // Create tenant here using the Tenant model
        // Vamos criar um tenant aqui usando o modelo Tenant
        // Exemplo: Tenant::create(['name' => 'Company Name', 'domain' => 'company-name.com']);

        $name = $this->ask('What is the name of the tenant?', fake()->company);
        $domain = $this->ask('What is the domain of the tenant?', request()->getHost());
        $email = $this->ask('What is the email of the tenant?', fake()->email);
        $status = $this->choice('What is the status of the tenant?', ['published', 'draft'], 'published');

        $tenant = Tenant::create([
            'name' => $name,
            'domain' => $domain,
            'email' => $email,
            'status' => $status,
        ]);

        $this->info("Tenant `{$name}` created successfully.");

        return $tenant;
    }

    protected function createUsers($tenant = null)
    {
        $this->comment('Creating users');

        // Create users here using the User model
        // Vamos criar usuários aqui usando o modelo User
        // Exemplo: User::create(['name' => 'John Doe', 'email' => 'jonh-doe@domino.com']);

        // Exemplo de criação de um usuário padrão
        $name = $this->ask('What is the name of the user?', 'John Doe');
        $email = $this->ask('What is the email of the user?', sprintf('john-doe@%s', request()->getHost()));
        $status = $this->choice('What is the status of the user?', ['published', 'draft'], DefaultStatus::PUBLISHED->value);
        $user =   User::factory()->create([
            'name' => $name,
            'email' => $email,
            'status' => $status,
        ]);

        $this->info('User `Admin` created successfully.');

        $user->tenant()->associate($tenant);

        return $user;
    }

    protected function createRole($role, $user = null, $permission = null)
    {
        $this->comment("Creating role `{$role}`");

        $newRole =  Role::create(['name' => $role, 'slug' => str($role)->slug(), 'description' => "Role for {$role}", 'special' => $permission]);

        if ($user) {
            $user->assignRole($newRole);
        }

        $this->info("Role `{$role}` created successfully.");

        if ($this->confirm('Create other role?')) {
            $role  = $this->ask('What role do you want to create?');
            $isAdministrator = $this->confirm('Is this role an administrator?');
            if ($isAdministrator) {
                $this->createRole($role, 'all-access');
            } else {
                $this->createRole($role);
            }
        }
    }

    protected function createPermission()
    {
        $this->comment("Creating permissions, such as `view users`, `edit users`, `delete users`, etc.");
        // Create permissions here using the Permission model
        // Vamos criar permissões aqui usando o modelo Permission, vamos usar como base a rotas do Laravel
        // Exemplo: Permission::create(['name' => 'view users']);

        // Exemplo de permissões para o CRUD de usuários, usando as rotas padrão do Laravel
        $routes = Route::getRoutes();
        $permissions = [];

        foreach ($routes as $route) {
            if (isset($route->action['as'])) {
                $name = str_replace('.', ' ', $route->action['as']);
                // Ignora rotas que não devem gerar permissões
                if (in_array($route->getName(), ['login', 'logout', 'register', 'password.request', 'password.email', 'password.reset', 'verification.notice', 'verification.verify', 'verification.send', 'sanctum.csrf-cookie'])) {
                    continue;
                }
                $name = ucwords($name);

                $slug = $route->action['as'];

                if (Permission::where('slug', $slug)->count()) {
                    continue;
                }

                $permissions[] = [
                    'name' => $name,
                    'slug' => $slug,
                    'description' => "Permissão para {$name}",
                    'status' => PermissionStatus::PUBLISHED,
                ];
            }
        }

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
