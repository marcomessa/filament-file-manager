<?php

namespace MmesDesign\FilamentFileManager;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class FileManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filament-file-manager.php', 'filament-file-manager');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-file-manager');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-file-manager');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \MmesDesign\FilamentFileManager\Console\Commands\ClearThumbnailsCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/filament-file-manager.php' => config_path('filament-file-manager.php'),
            ], 'filament-file-manager-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/filament-file-manager'),
            ], 'filament-file-manager-views');

            $this->publishes([
                __DIR__.'/../resources/lang' => lang_path('vendor/filament-file-manager'),
            ], 'filament-file-manager-translations');
        }

        Livewire::component('filament-file-manager', \MmesDesign\FilamentFileManager\Livewire\FileManager::class);
        Livewire::component('filament-file-manager-picker', \MmesDesign\FilamentFileManager\Livewire\FileManagerPicker::class);
    }
}
