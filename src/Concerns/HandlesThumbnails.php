<?php

namespace MmesDesign\FilamentFileManager\Concerns;

use Illuminate\Support\Facades\Log;
use MmesDesign\FilamentFileManager\Services\FileManagerService;
use MmesDesign\FilamentFileManager\Services\ThumbnailService;

trait HandlesThumbnails
{
    public function generateMissingThumbnails(): int
    {
        Log::debug('[Thumbnails] generateMissingThumbnails called', [
            'disk' => $this->currentDisk,
            'path' => $this->currentPath,
        ]);

        if (! config('filament-file-manager.thumbnails.enabled', true)) {
            Log::debug('[Thumbnails] disabled by config');

            return 0;
        }

        $listing = $this->getPaginatedListing()['listing'];
        $batchSize = (int) config('filament-file-manager.thumbnails.batch_size', 5);
        $thumbnailService = app(ThumbnailService::class);
        $generated = 0;
        $totalFiles = count($listing->files);
        $thumbnailableCount = 0;
        $missingCount = 0;

        foreach ($listing->files as $file) {
            if ($generated >= $batchSize) {
                Log::debug('[Thumbnails] batch limit reached', ['batchSize' => $batchSize]);
                break;
            }

            if ($file->isThumbnailable()) {
                $thumbnailableCount++;

                if (! $file->hasThumbnail()) {
                    $missingCount++;
                    $url = $thumbnailService->getThumbnailUrl($this->currentDisk, $file->path);
                    Log::debug('[Thumbnails] attempted generation', [
                        'file' => $file->path,
                        'result' => $url !== null ? 'success' : 'failed',
                    ]);

                    if ($url !== null) {
                        $generated++;
                    }
                }
            }
        }

        Log::debug('[Thumbnails] batch complete', [
            'totalFiles' => $totalFiles,
            'thumbnailable' => $thumbnailableCount,
            'missing' => $missingCount,
            'generated' => $generated,
        ]);

        if ($generated > 0) {
            app(FileManagerService::class)->clearDirectoryCache(
                $this->currentDisk,
                $this->currentPath,
            );
        }

        return $generated;
    }
}
