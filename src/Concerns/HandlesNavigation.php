<?php

namespace MmesDesign\FilamentFileManager\Concerns;

trait HandlesNavigation
{
    public string $currentPath = '';

    public function navigateTo(string $path): void
    {
        $this->currentPath = $path;
        $this->loadDirectory();
    }

    public function navigateUp(): void
    {
        if ($this->currentPath === '') {
            return;
        }

        $parent = dirname($this->currentPath);
        $this->currentPath = $parent === '.' ? '' : $parent;
        $this->loadDirectory();
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

    protected function getDiskLabel(): string
    {
        $disks = config('filament-file-manager.disks', []);

        return $disks[$this->currentDisk]['label'] ?? ucfirst($this->currentDisk);
    }
}
