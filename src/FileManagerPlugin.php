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

    public function hasAccess(mixed ...$context): bool
    {
        return $this->resolvePermission('access', ...$context);
    }

    public function canUserUpload(mixed ...$context): bool
    {
        return $this->resolvePermission('upload', ...$context);
    }

    public function canUserDelete(mixed ...$context): bool
    {
        return $this->resolvePermission('delete', ...$context);
    }

    public function canUserRename(mixed ...$context): bool
    {
        return $this->resolvePermission('rename', ...$context);
    }

    public function canUserMove(mixed ...$context): bool
    {
        return $this->resolvePermission('move', ...$context);
    }

    public function canUserDownload(mixed ...$context): bool
    {
        return $this->resolvePermission('download', ...$context);
    }

    public function canUserCreateFolder(mixed ...$context): bool
    {
        return $this->resolvePermission('createFolder', ...$context);
    }

    public function canUserBrowse(mixed ...$context): bool
    {
        return $this->resolvePermission('browse', ...$context);
    }

    /**
     * Build a permissions array for passing to Blade views.
     *
     * @return array{canUpload: bool, canDelete: bool, canRename: bool, canMove: bool, canDownload: bool, canCreateFolder: bool}
     */
    public function getPermissions(mixed ...$context): array
    {
        return [
            'canUpload' => $this->canUserUpload(...$context),
            'canDelete' => $this->canUserDelete(...$context),
            'canRename' => $this->canUserRename(...$context),
            'canMove' => $this->canUserMove(...$context),
            'canDownload' => $this->canUserDownload(...$context),
            'canCreateFolder' => $this->canUserCreateFolder(...$context),
            'canBrowse' => $this->canUserBrowse(...$context),
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
