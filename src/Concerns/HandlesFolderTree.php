<?php

namespace MmesDesign\FilamentFileManager\Concerns;

use Illuminate\Support\Facades\Storage;
use MmesDesign\FilamentFileManager\FileManagerPlugin;

trait HandlesFolderTree
{
    /** @var array<int, string> */
    public array $expandedFolders = [];

    public function toggleTreeFolder(string $path): void
    {
        if (in_array($path, $this->expandedFolders, true)) {
            $this->expandedFolders = array_values(array_filter(
                $this->expandedFolders,
                fn (string $p): bool => $p !== $path,
            ));
        } else {
            $this->expandedFolders[] = $path;
        }
    }

    public function navigateViaTree(string $path): void
    {
        $this->navigateTo($path);
        $this->ensureAncestorsExpanded($path);
    }

    public function ensureAncestorsExpanded(string $path): void
    {
        if ($path === '') {
            return;
        }

        $segments = explode('/', $path);
        $cumulative = '';

        foreach ($segments as $segment) {
            $cumulative = $cumulative === '' ? $segment : $cumulative.'/'.$segment;

            if (! in_array($cumulative, $this->expandedFolders, true)) {
                $this->expandedFolders[] = $cumulative;
            }
        }
    }

    /**
     * Build the folder tree nodes from expandedFolders. Called in render().
     *
     * @return array<string, array<int, array{name: string, path: string}>>
     */
    public function buildTreeNodes(): array
    {
        $disk = Storage::disk($this->currentDisk);
        $plugin = FileManagerPlugin::get();
        $nodes = [];

        $nodes[''] = $this->mapDirectories(
            $this->filterBrowsableDirectories($plugin, $disk->directories(''))
        );

        foreach ($this->expandedFolders as $folder) {
            $nodes[$folder] = $this->mapDirectories(
                $this->filterBrowsableDirectories($plugin, $disk->directories($folder))
            );
        }

        return $nodes;
    }

    /**
     * @param  array<int, string>  $directories
     * @return array<int, string>
     */
    protected function filterBrowsableDirectories(FileManagerPlugin $plugin, array $directories): array
    {
        return array_values(array_filter(
            $directories,
            fn (string $dir): bool => $plugin->canUserBrowse($this->currentDisk, $dir),
        ));
    }

    /**
     * @param  array<int, string>  $directories
     * @return array<int, array{name: string, path: string}>
     */
    protected function mapDirectories(array $directories): array
    {
        sort($directories);

        return array_map(
            fn (string $dir): array => [
                'name' => basename($dir),
                'path' => $dir,
            ],
            $directories,
        );
    }

    protected function resetFolderTree(): void
    {
        $this->expandedFolders = [];
    }
}
