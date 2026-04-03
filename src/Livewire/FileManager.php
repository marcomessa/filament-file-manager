<?php

namespace MmesDesign\FilamentFileManager\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Attributes\Url;
use Livewire\Component;
use MmesDesign\FilamentFileManager\Concerns\HandlesBulkOperations;
use MmesDesign\FilamentFileManager\Concerns\HandlesFileOperations;
use MmesDesign\FilamentFileManager\Concerns\HandlesFolderTree;
use MmesDesign\FilamentFileManager\Concerns\HandlesNavigation;
use MmesDesign\FilamentFileManager\Concerns\HandlesPagination;
use MmesDesign\FilamentFileManager\Concerns\HandlesSelection;
use MmesDesign\FilamentFileManager\Concerns\HandlesThumbnails;
use MmesDesign\FilamentFileManager\Concerns\HandlesUpload;
use MmesDesign\FilamentFileManager\Enums\SortDirection;
use MmesDesign\FilamentFileManager\Enums\ViewMode;
use MmesDesign\FilamentFileManager\FileManagerPlugin;
use MmesDesign\FilamentFileManager\Services\FileManagerService;
use MmesDesign\FilamentFileManager\Services\FileTypeResolver;

class FileManager extends Component implements HasActions, HasForms
{
    use HandlesBulkOperations;
    use HandlesFileOperations;
    use HandlesFolderTree;
    use HandlesNavigation;
    use HandlesPagination;
    use HandlesSelection;
    use HandlesThumbnails;
    use InteractsWithActions;
    use HandlesUpload, InteractsWithForms {
        HandlesUpload::_uploadErrored insteadof InteractsWithForms;
    }

    protected FileManagerService $fileManagerService;

    protected FileTypeResolver $fileTypeResolver;

    public string $currentDisk = '';

    #[Url]
    public string $viewMode = 'grid';

    #[Url]
    public string $sortField = 'name';

    #[Url]
    public string $sortDirection = 'asc';

    public function boot(FileManagerService $fileManagerService, FileTypeResolver $fileTypeResolver): void
    {
        $this->fileManagerService = $fileManagerService;
        $this->fileTypeResolver = $fileTypeResolver;
    }

    public function mount(): void
    {
        $this->currentDisk = FileManagerPlugin::get()->getDefaultDisk();
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

    public function refreshAction(): Action
    {
        return Action::make('refresh')
            ->label(__('filament-file-manager::file-manager.toolbar.refresh'))
            ->icon('heroicon-o-arrow-path')
            ->color('gray')
            ->action(fn () => null);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $paginated = $this->getPaginatedListing();

        return view('filament-file-manager::livewire.file-manager', [
            'listing' => $paginated['listing'],
            'totalFiles' => $paginated['totalFiles'],
            'hasMoreFiles' => $paginated['hasMoreFiles'],
            'treeNodes' => $this->buildTreeNodes(),
            'permissions' => FileManagerPlugin::get()->getPermissions($this->currentDisk, $this->currentPath),
        ]);
    }
}
