<?php

namespace MmesDesign\FilamentFileManager\Concerns;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use MmesDesign\FilamentFileManager\Enums\FileCategory;
use MmesDesign\FilamentFileManager\FileManagerPlugin;

trait HandlesFileOperations
{
    public function createFolderAction(): Action
    {
        return Action::make('createFolder')
            ->label(__('filament-file-manager::file-manager.toolbar.new_folder'))
            ->icon('heroicon-o-folder-plus')
            ->color('gray')
            ->visible(fn (): bool => FileManagerPlugin::get()->canUserCreateFolder($this->currentDisk, $this->currentPath))
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
                abort_unless(FileManagerPlugin::get()->canUserCreateFolder($this->currentDisk, $this->currentPath), 403, __('filament-file-manager::file-manager.messages.permission_denied'));

                try {
                    $this->fileManagerService->createFolder($this->currentDisk, $this->currentPath, $data['name']);

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
            ->visible(fn (): bool => FileManagerPlugin::get()->canUserRename($this->currentDisk, $this->currentPath))
            ->fillForm(fn (array $arguments): array => [
                'newName' => basename($arguments['path'] ?? ''),
                'originalExtension' => pathinfo(basename($arguments['path'] ?? ''), PATHINFO_EXTENSION),
            ])
            ->schema([
                Forms\Components\Hidden::make('originalExtension'),
                Forms\Components\TextInput::make('newName')
                    ->label(__('filament-file-manager::file-manager.labels.new_name'))
                    ->required()
                    ->maxLength(255)
                    ->regex('/^[^\/\\\\]+$/')
                    ->validationMessages([
                        'regex' => __('filament-file-manager::file-manager.labels.name_validation'),
                    ])
                    ->extraInputAttributes([
                        'x-init' => "setTimeout(() => { const dot = \$el.value.lastIndexOf('.'); \$el.focus(); if (dot > 0) { \$el.setSelectionRange(0, dot); } else { \$el.select(); } }, 50)",
                    ])
                    ->live(debounce: 500)
                    ->hint(function (?string $state, \Filament\Schemas\Components\Utilities\Get $get): ?string {
                        $original = $get('originalExtension');
                        if (! $original) {
                            return null;
                        }
                        $current = strtolower(pathinfo($state ?? '', PATHINFO_EXTENSION));
                        if ($current !== strtolower($original)) {
                            return __('filament-file-manager::file-manager.messages.extension_changed');
                        }

                        return null;
                    })
                    ->hintColor('warning')
                    ->hintIcon(function (?string $state, \Filament\Schemas\Components\Utilities\Get $get): ?string {
                        $original = $get('originalExtension');
                        if (! $original) {
                            return null;
                        }
                        $current = strtolower(pathinfo($state ?? '', PATHINFO_EXTENSION));

                        return $current !== strtolower($original) ? 'heroicon-o-exclamation-triangle' : null;
                    }),
            ])
            ->action(function (array $data, array $arguments): void {
                $path = $arguments['path'] ?? '';
                abort_unless(FileManagerPlugin::get()->canUserRename($this->currentDisk, $path), 403, __('filament-file-manager::file-manager.messages.permission_denied'));

                try {
                    $this->fileManagerService->rename($this->currentDisk, $path, $data['newName']);

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
            ->visible(fn (): bool => FileManagerPlugin::get()->canUserDelete($this->currentDisk, $this->currentPath))
            ->requiresConfirmation()
            ->modalHeading(__('filament-file-manager::file-manager.modals.confirm_deletion'))
            ->modalDescription(__('filament-file-manager::file-manager.modals.deletion_warning'))
            ->action(function (array $arguments): void {
                $path = $arguments['path'] ?? '';
                abort_unless(FileManagerPlugin::get()->canUserDelete($this->currentDisk, $path), 403, __('filament-file-manager::file-manager.messages.permission_denied'));
                $name = basename($path);

                $this->fileManagerService->delete($this->currentDisk, $path);

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

                $name = basename($path);
                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $category = $this->fileTypeResolver->resolve($name);
                $mimeType = $this->fileTypeResolver->mimeType($extension);
                $url = $this->fileManagerService->getUrl($this->currentDisk, $path);

                $content = null;
                if ($category === FileCategory::Code) {
                    $maxBytes = 50000;
                    $stream = Storage::disk($this->currentDisk)->readStream($path);
                    if ($stream !== null) {
                        $content = stream_get_contents($stream, $maxBytes);
                        fclose($stream);
                        $content = $content !== false ? $content : null;
                    }
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

    public function moveItem(string $path, string $destination): void
    {
        abort_unless(FileManagerPlugin::get()->canUserMove($this->currentDisk, $path), 403, __('filament-file-manager::file-manager.messages.permission_denied'));

        try {
            $this->fileManagerService->move($this->currentDisk, $path, $destination);

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
        abort_unless(FileManagerPlugin::get()->canUserDownload($this->currentDisk, $path), 403, __('filament-file-manager::file-manager.messages.permission_denied'));

        return $this->fileManagerService->download($this->currentDisk, $path);
    }
}
