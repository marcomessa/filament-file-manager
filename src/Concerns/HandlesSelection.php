<?php

namespace MmesDesign\FilamentFileManager\Concerns;

use MmesDesign\FilamentFileManager\Enums\SortDirection;
use MmesDesign\FilamentFileManager\Enums\SortField;
use MmesDesign\FilamentFileManager\Services\FileManagerService;

trait HandlesSelection
{
    /** @var array<int, string> */
    public array $selectedItems = [];

    public function toggleSelection(string $path): void
    {
        if (property_exists($this, 'multiple') && ! $this->multiple) {
            $this->selectedItems = in_array($path, $this->selectedItems, true)
                ? []
                : [$path];

            return;
        }

        $index = array_search($path, $this->selectedItems, true);

        if ($index !== false) {
            array_splice($this->selectedItems, $index, 1);
        } else {
            $this->selectedItems[] = $path;
        }
    }

    public function selectAll(): void
    {
        if (property_exists($this, 'multiple') && ! $this->multiple) {
            return;
        }

        $service = app(FileManagerService::class);

        $listing = $service->listDirectory(
            disk: $this->currentDisk,
            path: $this->currentPath,
            sortField: SortField::from($this->sortField),
            sortDirection: SortDirection::from($this->sortDirection),
        );

        $this->selectedItems = [];

        foreach ($listing->folders as $folder) {
            $this->selectedItems[] = $folder->path;
        }

        foreach ($listing->files as $file) {
            $this->selectedItems[] = $file->path;
        }
    }

    public function clearSelection(): void
    {
        $this->selectedItems = [];
    }

    public function isSelected(string $path): bool
    {
        return in_array($path, $this->selectedItems, true);
    }

    public function getSelectedCount(): int
    {
        return count($this->selectedItems);
    }
}
