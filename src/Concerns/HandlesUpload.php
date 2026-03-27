<?php

namespace MmesDesign\FilamentFileManager\Concerns;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Number;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use MmesDesign\FilamentFileManager\FileManagerPlugin;

trait HandlesUpload
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
            ->visible(fn (): bool => FileManagerPlugin::get()->canUserUpload($this->currentDisk, $this->currentPath))
            ->schema([
                Forms\Components\FileUpload::make('files')
                    ->label(__('filament-file-manager::file-manager.labels.file'))
                    ->multiple()
                    ->maxSize($maxSizeKb = (int) config('filament-file-manager.max_upload_size', 51200))
                    ->maxFiles(config('filament-file-manager.max_uploads_per_batch', 20))
                    ->storeFiles(false)
                    ->required()
                    ->validationMessages([
                        'max' => __('filament-file-manager::file-manager.messages.file_too_large', [
                            'max' => Number::fileSize($maxSizeKb * 1024, precision: 0),
                        ]),
                    ]),
            ])
            ->action(function (array $data): void {
                abort_unless(FileManagerPlugin::get()->canUserUpload($this->currentDisk, $this->currentPath), 403, __('filament-file-manager::file-manager.messages.permission_denied'));

                $service = $this->fileManagerService;
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

    /**
     * Override Livewire's _uploadErrored to replace the cryptic default message
     * (e.g. "The mountedActions.0.data.files.{uuid} failed to upload.")
     * with a human-readable error that includes the effective max file size.
     */
    public function _uploadErrored(string $name, ?string $errorsInJson, bool $isMultiple): void
    {
        $this->dispatch('upload:errored', name: $name)->self();

        $formatted = Number::fileSize($this->getEffectiveUploadLimit() * 1024, precision: 0);
        $message = __('filament-file-manager::file-manager.messages.upload_failed', ['max' => $formatted]);

        throw ValidationException::withMessages([$name => $message]);
    }

    /**
     * Get the effective upload limit in KB, considering the plugin config
     * and PHP's upload_max_filesize / post_max_size directives.
     */
    private function getEffectiveUploadLimit(): int
    {
        $pluginMax = (int) config('filament-file-manager.max_upload_size', 51200);

        $phpUploadMax = $this->phpIniToKb(ini_get('upload_max_filesize') ?: '2M');
        $phpPostMax = $this->phpIniToKb(ini_get('post_max_size') ?: '8M');

        return min($pluginMax, $phpUploadMax, $phpPostMax);
    }

    /**
     * Convert a PHP ini shorthand value (e.g. "128M", "2G") to kilobytes.
     */
    private function phpIniToKb(string $value): int
    {
        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $bytes = (int) $value;

        return match ($unit) {
            'g' => $bytes * 1024 * 1024,
            'm' => $bytes * 1024,
            'k' => $bytes,
            default => (int) ceil($bytes / 1024),
        };
    }
}
