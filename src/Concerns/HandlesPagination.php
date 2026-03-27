<?php

namespace MmesDesign\FilamentFileManager\Concerns;

use MmesDesign\FilamentFileManager\DTOs\DirectoryListing;
use MmesDesign\FilamentFileManager\DTOs\FolderItem;
use MmesDesign\FilamentFileManager\Enums\SortDirection;
use MmesDesign\FilamentFileManager\Enums\SortField;
use MmesDesign\FilamentFileManager\FileManagerPlugin;

trait HandlesPagination
{
    public int $filePage = 1;

    public function loadMore(): void
    {
        $service = $this->fileManagerService;

        $result = $service->listDirectoryPaginated(
            disk: $this->currentDisk,
            path: $this->currentPath,
            sortField: SortField::from($this->sortField),
            sortDirection: SortDirection::from($this->sortDirection),
            page: $this->filePage,
            perPage: $this->getPerPage(),
        );

        if (! $result['hasMore']) {
            return;
        }

        $this->filePage++;
    }

    public function resetPagination(): void
    {
        $this->filePage = 1;
    }

    public function getPerPage(): int
    {
        return (int) config('filament-file-manager.per_page', 50);
    }

    /**
     * @return array{listing: DirectoryListing, totalFiles: int, hasMoreFiles: bool}
     */
    protected function getPaginatedListing(): array
    {
        $service = $this->fileManagerService;

        $result = $service->listDirectoryPaginated(
            disk: $this->currentDisk,
            path: $this->currentPath,
            sortField: SortField::from($this->sortField),
            sortDirection: SortDirection::from($this->sortDirection),
            page: $this->filePage,
            perPage: $this->getPerPage(),
        );

        $plugin = FileManagerPlugin::get();
        $listing = $result['listing']->filterFolders(
            fn (FolderItem $folder): bool => $plugin->canUserBrowse($this->currentDisk, $folder->path),
        );

        return [
            'listing' => $listing,
            'totalFiles' => $result['totalFiles'],
            'hasMoreFiles' => $result['hasMore'],
        ];
    }
}
