<?php

namespace MmesDesign\FilamentFileManager;

use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FileManagerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('filament-file-manager')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations();
    }

    public function packageBooted(): void
    {
        Livewire::component('filament-file-manager', \MmesDesign\FilamentFileManager\Livewire\FileManager::class);
        Livewire::component('filament-file-manager-picker', \MmesDesign\FilamentFileManager\Livewire\FileManagerPicker::class);
    }
}
