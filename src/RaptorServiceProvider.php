<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Callcocam\Raptor\Commands\RaptorCommand;
use Callcocam\Raptor\Commands\RaptorSetupCommand;
use Spatie\LaravelPackageTools\Commands\InstallCommand;

class RaptorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('raptor')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoutes('web', 'api')
            ->hasTranslations()
            ->hasMigrations(
                'alter_users_table',
                'create_tenants_table',
                'create_addresses_table',
                'create_roles_table',
                'create_permissions_table',
                'create_permission_role_table',
                'create_role_user_table',
                'create_permission_user_table',
                'create_aborts_table',
            )
            ->hasCommand(RaptorCommand::class)
            ->hasCommand(RaptorSetupCommand::class)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishAssets()
                    ->publishMigrations()
                    ->publish('raptor:translations')
                    ->askToRunMigrations()
                    ->copyAndRegisterServiceProviderInApp()
                    ->askToStarRepoOnGitHub('callcocam/raptor')
                    ->endWith(function (InstallCommand $command) {
                        $command->call('raptor:setup');
                    });
            });
    }

    public function packageBooted()
    {
        $this->app->singleton('raptor', function () {
            return new Raptor();
        });
    }
}
