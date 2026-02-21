<?php

namespace MmesDesign\FilamentFileManager\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Attributes\Url;
use Livewire\Component;
use MmesDesign\FilamentFileManager\Concerns\HandlesFileOperations;
use MmesDesign\FilamentFileManager\Concerns\HandlesNavigation;
use MmesDesign\FilamentFileManager\Concerns\HandlesSelection;
use MmesDesign\FilamentFileManager\Enums\SortDirection;
use MmesDesign\FilamentFileManager\Enums\SortField;
use MmesDesign\FilamentFileManager\Enums\ViewMode;
use MmesDesign\FilamentFileManager\FileManagerPlugin;
use MmesDesign\FilamentFileManager\Services\FileManagerService;
use MmesDesign\FilamentFileManager\Services\ThumbnailService;

class FileManager extends Component implements HasActions, HasForms
{
    use HandlesFileOperations;
    use HandlesNavigation;
    use HandlesSelection;
    use InteractsWithActions;
    use InteractsWithForms;

    public string $currentDisk = '';

    #[Url]
    public string $viewMode = 'grid';

    #[Url]
    public string $sortField = 'name';

    #[Url]
    public string $sortDirection = 'asc';

    public function mount(): void
    {
        $this->currentDisk = FileManagerPlugin::get()->getDefaultDisk();
    }

    public function loadDirectory(): void
    {
        // Triggers a re-render which will fetch fresh data
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
    }

    public function getViewModeEnum(): ViewMode
    {
        return ViewMode::from($this->viewMode);
    }

    public function generateThumbnail(string $path): ?string
    {
        return app(ThumbnailService::class)->getThumbnailUrl($this->currentDisk, $path);
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
        $service = app(FileManagerService::class);

        $listing = $service->listDirectory(
            disk: $this->currentDisk,
            path: $this->currentPath,
            sortField: SortField::from($this->sortField),
            sortDirection: SortDirection::from($this->sortDirection),
        );

        return view('filament-file-manager::livewire.file-manager', [
            'listing' => $listing,
        ]);
    }
}
