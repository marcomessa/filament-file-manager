<?php

namespace MmesDesign\FilamentFileManager\Concerns;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use MmesDesign\FilamentFileManager\Enums\FileCategory;
use MmesDesign\FilamentFileManager\Services\FileManagerService;
use MmesDesign\FilamentFileManager\Services\FileTypeResolver;

trait HandlesFileOperations
{
    use WithFileUploads;

    /** @var array<int, TemporaryUploadedFile> */
    public array $pendingUploads = [];

    public function uploadFilesAction(): Action
    {
        return Action::make('uploadFiles')
            ->label(__('filament-file-manager::file-manager.toolbar.upload'))
            ->icon('heroicon-o-arrow-up-tray')
            ->color('primary')
            ->schema([
                Forms\Components\FileUpload::make('files')
                    ->label(__('filament-file-manager::file-manager.labels.file'))
                    ->multiple()
                    ->maxSize(config('filament-file-manager.max_upload_size', 51200))
                    ->maxFiles(config('filament-file-manager.max_uploads_per_batch', 20))
                    ->storeFiles(false)
                    ->required(),
            ])
            ->action(function (array $data): void {
                $service = app(FileManagerService::class);
                $uploaded = 0;
                $errors = [];

                foreach ($data['files'] as $file) {
                    try {
                        $service->upload($this->currentDisk, $this->currentPath, $file);
                        $uploaded++;
                    } catch (\InvalidArgumentException $e) {
                        $errors[] = $e->getMessage();
                    }
                }

                if ($uploaded > 0) {
                    Notification::make()
                        ->title(trans_choice('filament-file-manager::file-manager.messages.files_uploaded', $uploaded, ['count' => $uploaded]))
                        ->success()
                        ->send();
                }

                if (count($errors) > 0) {
                    Notification::make()
                        ->title(__('filament-file-manager::file-manager.messages.some_files_not_uploaded'))
                        ->body(implode("\n", $errors))
                        ->danger()
                        ->send();
                }
            });
    }

    public function createFolderAction(): Action
    {
        return Action::make('createFolder')
            ->label(__('filament-file-manager::file-manager.toolbar.new_folder'))
            ->icon('heroicon-o-folder-plus')
            ->color('gray')
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('filament-file-manager::file-manager.labels.folder_name'))
                    ->required()
                    ->maxLength(255)
                    ->regex('/^[^\/\\\\]+$/')
                    ->validationMessages([
                        'regex' => __('filament-file-manager::file-manager.labels.name_validation'),
                    ]),
            ])
            ->action(function (array $data): void {
                $service = app(FileManagerService::class);

                try {
                    $service->createFolder($this->currentDisk, $this->currentPath, $data['name']);

                    Notification::make()
                        ->title(__('filament-file-manager::file-manager.messages.folder_created', ['name' => $data['name']]))
                        ->success()
                        ->send();
                } catch (\InvalidArgumentException $e) {
                    Notification::make()
                        ->title($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    public function renameAction(): Action
    {
        return Action::make('rename')
            ->label(__('filament-file-manager::file-manager.actions.rename'))
            ->icon('heroicon-o-pencil')
            ->color('gray')
            ->schema([
                Forms\Components\TextInput::make('newName')
                    ->label(__('filament-file-manager::file-manager.labels.new_name'))
                    ->required()
                    ->maxLength(255)
                    ->regex('/^[^\/\\\\]+$/')
                    ->validationMessages([
                        'regex' => __('filament-file-manager::file-manager.labels.name_validation'),
                    ]),
            ])
            ->action(function (array $data, array $arguments): void {
                $service = app(FileManagerService::class);
                $path = $arguments['path'] ?? '';

                try {
                    $service->rename($this->currentDisk, $path, $data['newName']);

                    Notification::make()
                        ->title(__('filament-file-manager::file-manager.messages.renamed_successfully'))
                        ->success()
                        ->send();
                } catch (\InvalidArgumentException $e) {
                    Notification::make()
                        ->title($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    public function deleteItemAction(): Action
    {
        return Action::make('deleteItem')
            ->label(__('filament-file-manager::file-manager.actions.delete'))
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('filament-file-manager::file-manager.modals.confirm_deletion'))
            ->modalDescription(__('filament-file-manager::file-manager.modals.deletion_warning'))
            ->action(function (array $arguments): void {
                $service = app(FileManagerService::class);
                $path = $arguments['path'] ?? '';
                $name = basename($path);

                $service->delete($this->currentDisk, $path);

                Notification::make()
                    ->title(__('filament-file-manager::file-manager.messages.item_deleted', ['name' => $name]))
                    ->success()
                    ->send();
            });
    }

    public function previewAction(): Action
    {
        return Action::make('preview')
            ->label(__('filament-file-manager::file-manager.actions.preview'))
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('filament-file-manager::file-manager.modals.close'))
            ->modalWidth('4xl')
            ->modalHeading(fn (array $arguments): string => basename($arguments['path'] ?? ''))
            ->modalContent(function (array $arguments): \Illuminate\Contracts\View\View {
                $path = $arguments['path'] ?? '';
                $service = app(FileManagerService::class);
                $resolver = app(FileTypeResolver::class);

                $name = basename($path);
                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $category = $resolver->resolve($name);
                $mimeType = $resolver->mimeType($extension);
                $url = $service->getUrl($this->currentDisk, $path);

                $content = null;
                if ($category === FileCategory::Code) {
                    $raw = Storage::disk($this->currentDisk)->get($path);
                    $content = $raw !== null ? mb_substr($raw, 0, 50000) : null;
                }

                return view('filament-file-manager::components.file-preview', [
                    'name' => $name,
                    'extension' => $extension,
                    'category' => $category,
                    'mimeType' => $mimeType,
                    'url' => $url,
                    'content' => $content,
                ]);
            });
    }

    public function deleteSelectedAction(): Action
    {
        return Action::make('deleteSelected')
            ->label(__('filament-file-manager::file-manager.actions.delete_selected'))
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('filament-file-manager::file-manager.modals.confirm_deletion'))
            ->modalDescription(fn (): string => __('filament-file-manager::file-manager.modals.bulk_deletion_warning', ['count' => count($this->selectedItems)]))
            ->action(function (): void {
                $service = app(FileManagerService::class);
                $count = $service->deleteBulk($this->currentDisk, $this->selectedItems);
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
            ->schema([
                Forms\Components\TextInput::make('destination')
                    ->label(__('filament-file-manager::file-manager.labels.destination_folder'))
                    ->placeholder(__('filament-file-manager::file-manager.labels.destination_placeholder'))
                    ->helperText(__('filament-file-manager::file-manager.labels.destination_helper'))
                    ->maxLength(255),
            ])
            ->action(function (array $data): void {
                $service = app(FileManagerService::class);
                $destination = $data['destination'] ?? '';
                $count = $service->moveBulk($this->currentDisk, $this->selectedItems, $destination);
                $this->selectedItems = [];

                Notification::make()
                    ->title(__('filament-file-manager::file-manager.messages.items_moved', ['count' => $count]))
                    ->success()
                    ->send();
            });
    }

    public function moveItem(string $path, string $destination): void
    {
        $service = app(FileManagerService::class);

        try {
            $service->move($this->currentDisk, $path, $destination);

            Notification::make()
                ->title(__('filament-file-manager::file-manager.messages.item_moved', ['name' => basename($path), 'destination' => $destination ?: 'root']))
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('filament-file-manager::file-manager.messages.move_error'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function downloadFile(string $path): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $service = app(FileManagerService::class);

        return $service->download($this->currentDisk, $path);
    }
}
