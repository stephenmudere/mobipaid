<?php

namespace Stephenmudere\Mobipaid;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Stephenmudere\Mobipaid\Commands\MobipaidCommand;

class MobipaidServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('mobipaid')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_mobipaid_table')
            ->hasCommand(MobipaidCommand::class);
    }
}
