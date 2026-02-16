<?php

namespace SimoneBianco\LaravelProcesses;

use SimoneBianco\LaravelProcesses\Models\Process;
use SimoneBianco\LaravelProcesses\Observers\ProcessObserver;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelProcessesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-processes')
            ->hasMigrations([
                'create_processes_table',
            ])
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('simone-bianco/laravel-processes');
            });
    }

    public function packageBooted(): void
    {
        Process::observe(ProcessObserver::class);
    }
}
