<?php

namespace MmesDesign\FilamentFileManager\Concerns;

use MmesDesign\FilamentFileManager\FileManagerPlugin;

trait HandlesNavigation
{
    public string $currentPath = '';

    public function navigateTo(string $path): void
    {
        if ($path !== '' && ! FileManagerPlugin::get()->canUserBrowse($this->currentDisk, $path)) {
            return;
        }

        $this->currentPath = $path;
        $this->resetSelectionOnNavigate();
        $this->loadDirectory();

        if (method_exists($this, 'ensureAncestorsExpanded')) {
            $this->ensureAncestorsExpanded($path);
        }
    }

    public function navigateUp(): void
    {
        if ($this->currentPath === '') {
            return;
        }

        $parent = dirname($this->currentPath);
        $this->currentPath = $parent === '.' ? '' : $parent;
        $this->resetSelectionOnNavigate();
        $this->loadDirectory();
    }

    /**
     * Clear the selection when the current folder changes, so bulk actions
     * never apply to items the user can no longer see.
     */
    protected function resetSelectionOnNavigate(): void
    {
        $this->selectedItems = [];
    }

    /**
     * @return array<int, array{name: string, path: string}>
     */
    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            ['name' => $this->getDiskLabel(), 'path' => ''],
        ];

        if ($this->currentPath === '') {
            return $breadcrumbs;
        }

        $segments = explode('/', $this->currentPath);
        $cumulativePath = '';

        foreach ($segments as $segment) {
            $cumulativePath = $cumulativePath === '' ? $segment : $cumulativePath.'/'.$segment;

            $breadcrumbs[] = [
                'name' => $segment,
                'path' => $cumulativePath,
            ];
        }

        return $breadcrumbs;
    }

    public function getDiskLabel(): string
    {
        $disks = config('filament-file-manager.disks', []);

        return $disks[$this->currentDisk]['label'] ?? ucfirst($this->currentDisk);
    }
}
