<?php

namespace MmesDesign\FilamentFileManager\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use MmesDesign\FilamentFileManager\Concerns\HandlesNavigation;
use MmesDesign\FilamentFileManager\Concerns\HandlesPagination;
use MmesDesign\FilamentFileManager\Concerns\HandlesSelection;
use MmesDesign\FilamentFileManager\Enums\FileCategory;
use MmesDesign\FilamentFileManager\Enums\SortDirection;
use MmesDesign\FilamentFileManager\Enums\ViewMode;
use MmesDesign\FilamentFileManager\Services\FileManagerService;

class FileManagerPicker extends Component
{
    use HandlesNavigation;
    use HandlesPagination;
    use HandlesSelection;

    protected FileManagerService $fileManagerService;

    public string $currentDisk = '';

    public string $viewMode = 'grid';

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    public bool $multiple = false;

    public ?string $fieldId = null;

    /** @var array<int, string> */
    public array $acceptedCategories = [];

    public function boot(FileManagerService $fileManagerService): void
    {
        $this->fileManagerService = $fileManagerService;
    }

    /**
     * @param  string|array<int, string>|null  $selectedPaths
     */
    public function mount(?string $disk = null, string|array|null $selectedPaths = null): void
    {
        $this->currentDisk = $disk ?: config('filament-file-manager.disk', 'public');

        $paths = match (true) {
            is_string($selectedPaths) => [$selectedPaths],
            is_array($selectedPaths) => $selectedPaths,
            default => [],
        };

        $paths = array_values(array_filter($paths));

        if ($paths !== []) {
            $this->selectedItems = $paths;
            $this->currentPath = dirname($paths[0]);

            if ($this->currentPath === '.') {
                $this->currentPath = '';
            }
        }
    }

    public function loadDirectory(): void
    {
        $this->resetPagination();
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    public function setSortField(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = SortDirection::from($this->sortDirection)->toggle()->value;
        } else {
            $this->sortField = $field;
            $this->sortDirection = SortDirection::Asc->value;
        }

        $this->resetPagination();
    }

    public function getViewModeEnum(): ViewMode
    {
        return ViewMode::from($this->viewMode);
    }

    public function confirmSelection(): void
    {
        $paths = $this->multiple
            ? $this->selectedItems
            : ($this->selectedItems[0] ?? null);

        $this->dispatch('file-picker-selected', paths: $paths, fieldId: $this->fieldId);
    }

    public function render(): View
    {
        $paginated = $this->getPaginatedListing();

        $listing = $paginated['listing'];
        $totalFiles = $paginated['totalFiles'];
        $hasMoreFiles = $paginated['hasMoreFiles'];

        if ($this->acceptedCategories !== []) {
            $accepted = array_map(
                fn (string $value): FileCategory => FileCategory::from($value),
                $this->acceptedCategories,
            );

            $listing = $listing->filterFiles(
                fn ($file): bool => in_array($file->category, $accepted, true),
            );
        }

        return view('filament-file-manager::livewire.file-manager-picker', [
            'listing' => $listing,
            'totalFiles' => $totalFiles,
            'hasMoreFiles' => $hasMoreFiles,
        ]);
    }
}
