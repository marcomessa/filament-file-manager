<?php

namespace MmesDesign\FilamentFileManager\Concerns;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use MmesDesign\FilamentFileManager\FileManagerPlugin;
use MmesDesign\FilamentFileManager\Forms\Components\FolderTreePicker;

trait HandlesBulkOperations
{
    public function deleteSelectedAction(): Action
    {
        return Action::make('deleteSelected')
            ->label(__('filament-file-manager::file-manager.actions.delete_selected'))
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->visible(fn (): bool => FileManagerPlugin::get()->canUserDelete($this->currentDisk, $this->currentPath))
            ->requiresConfirmation()
            ->modalHeading(__('filament-file-manager::file-manager.modals.confirm_deletion'))
            ->modalDescription(fn (): string => __('filament-file-manager::file-manager.modals.bulk_deletion_warning', ['count' => count($this->selectedItems)]))
            ->action(function (): void {
                abort_unless(FileManagerPlugin::get()->canUserDelete($this->currentDisk, $this->currentPath), 403, __('filament-file-manager::file-manager.messages.permission_denied'));

                $count = $this->fileManagerService->deleteBulk($this->currentDisk, $this->selectedItems);
                $this->selectedItems = [];

                Notification::make()
                    ->title(__('filament-file-manager::file-manager.messages.items_deleted', ['count' => $count]))
                    ->success()
                    ->send();
            });
    }

    public function moveSelectedAction(): Action
    {
        return Action::make('moveSelected')
            ->label(__('filament-file-manager::file-manager.actions.move_selected'))
            ->icon('heroicon-o-arrow-right')
            ->color('gray')
            ->visible(fn (): bool => FileManagerPlugin::get()->canUserMove($this->currentDisk, $this->currentPath))
            ->schema([
                FolderTreePicker::make('destination')
                    ->label(__('filament-file-manager::file-manager.labels.destination_folder'))
                    ->disk($this->currentDisk)
                    ->default(''),
            ])
            ->action(function (array $data): void {
                abort_unless(FileManagerPlugin::get()->canUserMove($this->currentDisk, $this->currentPath), 403, __('filament-file-manager::file-manager.messages.permission_denied'));

                $destination = $data['destination'] ?? '';
                $count = $this->fileManagerService->moveBulk($this->currentDisk, $this->selectedItems, $destination);
                $this->selectedItems = [];

                Notification::make()
                    ->title(__('filament-file-manager::file-manager.messages.items_moved', ['count' => $count]))
                    ->success()
                    ->send();
            });
    }
}
