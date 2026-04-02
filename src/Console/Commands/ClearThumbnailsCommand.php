<?php

namespace MmesDesign\FilamentFileManager\Console\Commands;

use Illuminate\Console\Command;
use MmesDesign\FilamentFileManager\Services\FileManagerService;

class ClearThumbnailsCommand extends Command
{
    protected $signature = 'filament-file-manager:clear-thumbnails {--disk= : The disk to clear thumbnails from}';

    protected $description = 'Delete all generated thumbnails from the specified disk';

    public function handle(FileManagerService $fileManagerService): int
    {
        $disk = $this->option('disk') ?: config('filament-file-manager.disk', 'public');
        $directory = config('filament-file-manager.thumbnails.directory', '.thumbnails');

        try {
            $exists = $fileManagerService->directoryExists($disk, $directory);
        } catch (\RuntimeException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        if (! $exists) {
            $this->components->info(__('filament-file-manager::file-manager.commands.no_thumbnails', ['disk' => $disk]));

            return self::SUCCESS;
        }

        $fileManagerService->delete($disk, $directory);
        $fileManagerService->invalidateDiskCache($disk);

        $this->components->info(__('filament-file-manager::file-manager.commands.thumbnails_cleared', ['disk' => $disk]));

        return self::SUCCESS;
    }
}
