<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\Raptor;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
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
           
            ->hasMigrations(
                'alter_users_table',
                'create_tenants_table',
                'create_addresses_table',
                'create_roles_table',
                'create_permissions_table',
                'create_permission_role_table',
                'create_role_user_table',
                'create_permission_user_table'
            )
            ->hasCommand(RaptorSetupCommand::class)
            ->hasRoutes('web','api')
            ->hasCommand(RaptorSetupCommand::class)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishAssets()
                    ->publishMigrations()
                    ->publish('lara-gatekeeper:translations')
                    ->askToRunMigrations()
                    ->copyAndRegisterServiceProviderInApp()
                    ->askToStarRepoOnGitHub('callcocam/raptor')
                    ->endWith(function (InstallCommand $command) {
                        $command->call('raptor:setup');
                    });
            });
    }

    public function packageRegistered()
    {
        $this->app->register(\Callcocam\Raptor\Core\Shinobi\ShinobiServiceProvider::class);
        $this->app->register(\Callcocam\Raptor\Core\Landlord\LandlordServiceProvider::class);
    }
}
