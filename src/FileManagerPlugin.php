<?php

namespace MmesDesign\FilamentFileManager;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Assets\Css;
use MmesDesign\FilamentFileManager\Filament\Pages\FileManagerPage;

class FileManagerPlugin implements Plugin
{
    protected string $defaultDisk = '';

    protected ?string $navigationGroup = null;

    protected ?string $navigationIcon = null;

    protected ?int $navigationSort = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        try {
            /** @var static $plugin */
            $plugin = filament(app(static::class)->getId());

            return $plugin;
        } catch (\LogicException) {
            return static::make();
        }
    }

    public function getId(): string
    {
        return 'filament-file-manager';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->pages([
                FileManagerPage::class,
            ])
            ->assets([
                Css::make('filament-file-manager-styles', __DIR__.'/../resources/dist/file-manager.css'),
            ]);
    }

    public function boot(Panel $panel): void {}

    public function defaultDisk(string $disk): static
    {
        $this->defaultDisk = $disk;

        return $this;
    }

    public function getDefaultDisk(): string
    {
        return $this->defaultDisk ?: config('filament-file-manager.disk', 'public');
    }

    public function navigationGroup(?string $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function getNavigationGroup(): ?string
    {
        return $this->navigationGroup;
    }

    public function navigationIcon(string $icon): static
    {
        $this->navigationIcon = $icon;

        return $this;
    }

    public function getNavigationIcon(): string
    {
        return $this->navigationIcon ?? 'heroicon-o-folder-open';
    }

    public function navigationSort(?int $sort): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    public function getNavigationSort(): ?int
    {
        return $this->navigationSort;
    }
}
