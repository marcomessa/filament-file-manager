<?php

namespace MmesDesign\FilamentFileManager\Concerns;

use MmesDesign\FilamentFileManager\DTOs\DirectoryListing;
use MmesDesign\FilamentFileManager\Enums\SortDirection;
use MmesDesign\FilamentFileManager\Enums\SortField;
use MmesDesign\FilamentFileManager\Services\FileManagerService;

trait HandlesPagination
{
    public int $filePage = 1;

    public function loadMore(): void
    {
        $service = app(FileManagerService::class);

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
        $service = app(FileManagerService::class);

        $result = $service->listDirectoryPaginated(
            disk: $this->currentDisk,
            path: $this->currentPath,
            sortField: SortField::from($this->sortField),
            sortDirection: SortDirection::from($this->sortDirection),
            page: $this->filePage,
            perPage: $this->getPerPage(),
        );

        return [
            'listing' => $result['listing'],
            'totalFiles' => $result['totalFiles'],
            'hasMoreFiles' => $result['hasMore'],
        ];
    }
}
