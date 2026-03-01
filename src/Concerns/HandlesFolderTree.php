<?php

namespace MmesDesign\FilamentFileManager\Concerns;

use Illuminate\Support\Facades\Storage;

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
        $nodes = [];

        // Always load root
        $nodes[''] = $this->mapDirectories($disk->directories(''));

        // Load children for each expanded folder
        foreach ($this->expandedFolders as $folder) {
            $nodes[$folder] = $this->mapDirectories($disk->directories($folder));
        }

        return $nodes;
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
