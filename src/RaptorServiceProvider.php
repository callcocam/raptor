<?php

namespace Callcocam\Raptor;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Callcocam\Raptor\Commands\RaptorCommand;

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
            ->hasRoutes('web','api')
            ->hasTranslations()
            ->hasMigration('create_raptor_table')
            ->hasCommand(RaptorCommand::class);
    }
}
