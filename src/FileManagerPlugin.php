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

    protected \Closure|bool|null $canAccessUsing = null;

    protected \Closure|bool|null $canUploadUsing = null;

    protected \Closure|bool|null $canDeleteUsing = null;

    protected \Closure|bool|null $canRenameUsing = null;

    protected \Closure|bool|null $canMoveUsing = null;

    protected \Closure|bool|null $canDownloadUsing = null;

    protected \Closure|bool|null $canCreateFolderUsing = null;

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
                Css::make('filament-file-manager-styles', __DIR__ . '/../resources/dist/file-manager.css'),
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

    public function canAccess(\Closure|bool $callback): static
    {
        $this->canAccessUsing = $callback;

        return $this;
    }

    public function canUpload(\Closure|bool $callback): static
    {
        $this->canUploadUsing = $callback;

        return $this;
    }

    public function canDelete(\Closure|bool $callback): static
    {
        $this->canDeleteUsing = $callback;

        return $this;
    }

    public function canRename(\Closure|bool $callback): static
    {
        $this->canRenameUsing = $callback;

        return $this;
    }

    public function canMove(\Closure|bool $callback): static
    {
        $this->canMoveUsing = $callback;

        return $this;
    }

    public function canDownload(\Closure|bool $callback): static
    {
        $this->canDownloadUsing = $callback;

        return $this;
    }

    public function canCreateFolder(\Closure|bool $callback): static
    {
        $this->canCreateFolderUsing = $callback;

        return $this;
    }

    public function hasAccess(): bool
    {
        return $this->evaluateAbility($this->canAccessUsing);
    }

    public function canUserUpload(): bool
    {
        return $this->evaluateAbility($this->canUploadUsing);
    }

    public function canUserDelete(): bool
    {
        return $this->evaluateAbility($this->canDeleteUsing);
    }

    public function canUserRename(): bool
    {
        return $this->evaluateAbility($this->canRenameUsing);
    }

    public function canUserMove(): bool
    {
        return $this->evaluateAbility($this->canMoveUsing);
    }

    public function canUserDownload(): bool
    {
        return $this->evaluateAbility($this->canDownloadUsing);
    }

    public function canUserCreateFolder(): bool
    {
        return $this->evaluateAbility($this->canCreateFolderUsing);
    }

    /**
     * Build a permissions array for passing to Blade views.
     *
     * @return array{canUpload: bool, canDelete: bool, canRename: bool, canMove: bool, canDownload: bool, canCreateFolder: bool}
     */
    public function getPermissions(): array
    {
        return [
            'canUpload' => $this->canUserUpload(),
            'canDelete' => $this->canUserDelete(),
            'canRename' => $this->canUserRename(),
            'canMove' => $this->canUserMove(),
            'canDownload' => $this->canUserDownload(),
            'canCreateFolder' => $this->canUserCreateFolder(),
        ];
    }

    /**
     * Evaluate a permission closure or boolean. Defaults to true (backward compat).
     */
    protected function evaluateAbility(\Closure|bool|null $ability, mixed ...$context): bool
    {
        if ($ability === null) {
            return true;
        }

        if (is_bool($ability)) {
            return $ability;
        }

        return (bool) $ability(...$context);
    }

    /**
     * Hook for Pro package to override permission resolution.
     */
    protected function resolvePermission(string $ability, mixed ...$context): bool
    {
        return match ($ability) {
            'access' => $this->evaluateAbility($this->canAccessUsing, ...$context),
            'upload' => $this->evaluateAbility($this->canUploadUsing, ...$context),
            'delete' => $this->evaluateAbility($this->canDeleteUsing, ...$context),
            'rename' => $this->evaluateAbility($this->canRenameUsing, ...$context),
            'move' => $this->evaluateAbility($this->canMoveUsing, ...$context),
            'download' => $this->evaluateAbility($this->canDownloadUsing, ...$context),
            'createFolder' => $this->evaluateAbility($this->canCreateFolderUsing, ...$context),
            default => true,
        };
    }
}
