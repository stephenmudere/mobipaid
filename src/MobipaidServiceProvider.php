<?php

namespace Stephenmudere\Mobipaid;

use Stephenmudere\LaravelPackageTools\Package;
use Stephenmudere\LaravelPackageTools\PackageServiceProvider;
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
            ->namespace('\\Stephenmudere\\Mobipaid\\')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_mobipaid_table')
            ->hasCommand(MobipaidCommand::class);
    }
}
