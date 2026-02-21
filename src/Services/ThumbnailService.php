<?php

namespace MmesDesign\FilamentFileManager\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use MmesDesign\FilamentFileManager\DTOs\FileItem;

class ThumbnailService
{
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver);
    }

    /**
     * Generate a thumbnail (if it doesn't exist) and return its URL.
     */
    public function getThumbnailUrl(string $disk, string $path): ?string
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $thumbnailPath = $this->thumbnailPath($path);
        $storage = Storage::disk($disk);

        if (! $storage->exists($thumbnailPath)) {
            if (! $this->generate($disk, $path)) {
                return null;
            }
        }

        return $this->resolveUrl($disk, $thumbnailPath);
    }

    /**
     * Return the thumbnail URL only if the thumbnail already exists (no generation).
     */
    public function getExistingThumbnailUrl(string $disk, string $path): ?string
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $thumbnailPath = $this->thumbnailPath($path);

        if (! Storage::disk($disk)->exists($thumbnailPath)) {
            return null;
        }

        return $this->resolveUrl($disk, $thumbnailPath);
    }

    /**
     * Generate a thumbnail for the given file.
     */
    public function generate(string $disk, string $path): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $storage = Storage::disk($disk);

        if (! $storage->exists($path)) {
            return false;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (! in_array($extension, FileItem::THUMBNAILABLE_EXTENSIONS, true)) {
            return false;
        }

        try {
            $contents = $storage->get($path);
            $width = (int) config('filament-file-manager.thumbnails.width', 200);
            $height = (int) config('filament-file-manager.thumbnails.height', 200);
            $quality = (int) config('filament-file-manager.thumbnails.quality', 80);

            $image = $this->imageManager->read($contents);
            $image->coverDown($width, $height);

            $encoded = $image->encodeByExtension($extension, quality: $quality);

            $thumbnailPath = $this->thumbnailPath($path);
            $storage->put($thumbnailPath, (string) $encoded);

            return true;
        } catch (\Throwable $e) {
            Log::warning("Failed to generate thumbnail for {$disk}:{$path}", [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Delete the thumbnail for a given file path.
     */
    public function delete(string $disk, string $path): void
    {
        $thumbnailPath = $this->thumbnailPath($path);
        $storage = Storage::disk($disk);

        if ($storage->exists($thumbnailPath)) {
            $storage->delete($thumbnailPath);
        }
    }

    /**
     * Build the thumbnail storage path for a given file path.
     */
    public function thumbnailPath(string $path): string
    {
        $directory = config('filament-file-manager.thumbnails.directory', '.thumbnails');

        return $directory.'/'.$path;
    }

    protected function isEnabled(): bool
    {
        return (bool) config('filament-file-manager.thumbnails.enabled', true);
    }

    protected function resolveUrl(string $disk, string $thumbnailPath): ?string
    {
        try {
            if (config("filesystems.disks.{$disk}.visibility") === 'public') {
                return Storage::disk($disk)->url($thumbnailPath);
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning("Failed to resolve thumbnail URL for {$disk}:{$thumbnailPath}", [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
