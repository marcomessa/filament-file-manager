<?php

namespace MmesDesign\FilamentFileManager\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use MmesDesign\FilamentFileManager\DTOs\DirectoryListing;
use MmesDesign\FilamentFileManager\DTOs\FileItem;
use MmesDesign\FilamentFileManager\DTOs\FolderItem;
use MmesDesign\FilamentFileManager\Enums\SortDirection;
use MmesDesign\FilamentFileManager\Enums\SortField;
use MmesDesign\FilamentFileManager\Support\PathSanitizer;

class FileManagerService
{
    public function __construct(
        protected PathSanitizer $pathSanitizer,
        protected FileTypeResolver $fileTypeResolver,
        protected ThumbnailService $thumbnailService,
    ) {}

    public function listDirectory(
        string $disk,
        string $path = '',
        SortField $sortField = SortField::Name,
        SortDirection $sortDirection = SortDirection::Asc,
    ): DirectoryListing {
        return $this->getCachedListing($disk, $path, $sortField, $sortDirection);
    }

    /**
     * @return array{listing: DirectoryListing, totalFiles: int, hasMore: bool}
     */
    public function listDirectoryPaginated(
        string $disk,
        string $path = '',
        SortField $sortField = SortField::Name,
        SortDirection $sortDirection = SortDirection::Asc,
        int $page = 1,
        ?int $perPage = null,
    ): array {
        $perPage ??= (int) config('filament-file-manager.per_page', 50);
        $listing = $this->listDirectory($disk, $path, $sortField, $sortDirection);

        $totalFiles = count($listing->files);
        $limit = $page * $perPage;
        $paginatedFiles = array_slice($listing->files, 0, $limit);

        return [
            'listing' => new DirectoryListing(
                path: $listing->path,
                disk: $listing->disk,
                folders: $listing->folders,
                files: $paginatedFiles,
            ),
            'totalFiles' => $totalFiles,
            'hasMore' => $limit < $totalFiles,
        ];
    }

    protected function getCachedListing(
        string $disk,
        string $path,
        SortField $sortField,
        SortDirection $sortDirection,
    ): DirectoryListing {
        $path = $path === '' ? '' : $this->pathSanitizer->sanitize($path);
        $version = $this->getDiskCacheVersion($disk);
        $cacheKey = "fm:{$disk}:v{$version}:{$path}:{$sortField->value}:{$sortDirection->value}";

        return Cache::remember($cacheKey, 60, function () use ($disk, $path, $sortField, $sortDirection): DirectoryListing {
            return $this->buildDirectoryListing($disk, $path, $sortField, $sortDirection);
        });
    }

    protected function buildDirectoryListing(
        string $disk,
        string $path,
        SortField $sortField,
        SortDirection $sortDirection,
    ): DirectoryListing {
        $storage = $this->disk($disk);

        $rawData = $this->fetchDirectoryContents($storage, $path);

        $folders = $this->buildFolderItems($rawData['directories'], $storage);
        $files = $this->buildFileItems($rawData['files'], $storage, $disk);

        $folders = $this->sortFolders($folders, $sortField, $sortDirection);
        $files = $this->sortFiles($files, $sortField, $sortDirection);

        return new DirectoryListing(
            path: $path,
            disk: $disk,
            folders: $folders,
            files: $files,
        );
    }

    public function getUrl(string $disk, string $path): ?string
    {
        $path = $this->pathSanitizer->sanitize($path);

        if ($this->hasPublicVisibility($disk)) {
            return $this->disk($disk)->url($path);
        }

        return null;
    }

    public function exists(string $disk, string $path): bool
    {
        $path = $this->pathSanitizer->sanitize($path);

        return $this->disk($disk)->exists($path);
    }

    public function directoryExists(string $disk, string $path): bool
    {
        $path = $this->pathSanitizer->sanitize($path);

        return $this->disk($disk)->directoryExists($path);
    }

    /**
     * Upload a file to the given disk and directory.
     *
     * @throws \InvalidArgumentException
     */
    public function upload(string $disk, string $directory, UploadedFile $file, ?string $filename = null): string
    {
        $directory = $directory === '' ? '' : $this->pathSanitizer->sanitize($directory);
        $name = $filename ?? $file->getClientOriginalName();

        if ($this->pathSanitizer->isExtensionDenied($name)) {
            throw new \InvalidArgumentException(__('filament-file-manager::file-manager.messages.file_type_not_allowed', ['name' => $name]));
        }

        $path = $this->disk($disk)->putFileAs($directory, $file, $name);
        $this->invalidateCache($disk, $directory);

        return $path;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function createFolder(string $disk, string $directory, string $name): string
    {
        $directory = $directory === '' ? '' : $this->pathSanitizer->sanitize($directory);
        $path = $this->pathSanitizer->join($directory, $name);

        if ($this->disk($disk)->directoryExists($path)) {
            throw new \InvalidArgumentException(__('filament-file-manager::file-manager.messages.folder_already_exists', ['name' => $name]));
        }

        $this->disk($disk)->makeDirectory($path);
        $this->invalidateCache($disk, $directory);

        return $path;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function rename(string $disk, string $path, string $newName): string
    {
        $path = $this->pathSanitizer->sanitize($path);
        $directory = dirname($path) === '.' ? '' : dirname($path);
        $newPath = $this->pathSanitizer->join($directory, $newName);

        if ($this->pathSanitizer->isExtensionDenied($newName)) {
            throw new \InvalidArgumentException(__('filament-file-manager::file-manager.messages.file_type_not_allowed', ['name' => $newName]));
        }

        $storage = $this->disk($disk);

        if ($storage->exists($newPath) || $storage->directoryExists($newPath)) {
            throw new \InvalidArgumentException(__('filament-file-manager::file-manager.messages.name_already_exists', ['name' => $newName]));
        }

        $storage->move($path, $newPath);
        $this->thumbnailService->delete($disk, $path);
        $this->invalidateCache($disk, $directory);

        return $newPath;
    }

    public function delete(string $disk, string $path): void
    {
        $path = $this->pathSanitizer->sanitize($path);
        $directory = dirname($path) === '.' ? '' : dirname($path);
        $storage = $this->disk($disk);

        if ($storage->directoryExists($path)) {
            $storage->deleteDirectory($path);
        } else {
            $this->thumbnailService->delete($disk, $path);
            $storage->delete($path);
        }

        $this->invalidateCache($disk, $directory);
    }

    /**
     * @param  array<int, string>  $paths
     */
    public function deleteBulk(string $disk, array $paths): int
    {
        $count = 0;

        foreach ($paths as $path) {
            $this->delete($disk, $path);
            $count++;
        }

        return $count;
    }

    /**
     * @param  array<int, string>  $paths
     */
    public function moveBulk(string $disk, array $paths, string $destinationDirectory): int
    {
        $count = 0;

        foreach ($paths as $path) {
            $this->move($disk, $path, $destinationDirectory);
            $count++;
        }

        return $count;
    }

    public function download(string $disk, string $path): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $path = $this->pathSanitizer->sanitize($path);

        return $this->disk($disk)->download($path);
    }

    /**
     * Move a file or folder to a new directory.
     */
    public function move(string $disk, string $path, string $destinationDirectory): string
    {
        $path = $this->pathSanitizer->sanitize($path);
        $destinationDirectory = $destinationDirectory === '' ? '' : $this->pathSanitizer->sanitize($destinationDirectory);
        $name = basename($path);
        $sourceDirectory = dirname($path) === '.' ? '' : dirname($path);
        $newPath = $destinationDirectory === '' ? $name : $destinationDirectory.'/'.$name;

        $this->disk($disk)->move($path, $newPath);
        $this->thumbnailService->delete($disk, $path);
        $this->invalidateCache($disk, $sourceDirectory);
        $this->invalidateCache($disk, $destinationDirectory);

        return $newPath;
    }

    /**
     * @return array{directories: array<int, string>, files: array<int, string>}
     */
    protected function fetchDirectoryContents(Filesystem $storage, string $path): array
    {
        return [
            'directories' => $storage->directories($path),
            'files' => $storage->files($path),
        ];
    }

    /**
     * @param  array<int, string>  $directories
     * @return array<int, FolderItem>
     */
    protected function buildFolderItems(array $directories, Filesystem $storage): array
    {
        return array_map(function (string $directory) use ($storage): FolderItem {
            try {
                $lastModified = $storage->lastModified($directory);
            } catch (\League\Flysystem\UnableToRetrieveMetadata) {
                $lastModified = 0;
            }

            return new FolderItem(
                name: basename($directory),
                path: $directory,
                lastModified: $lastModified,
            );
        }, $directories);
    }

    /**
     * @param  array<int, string>  $files
     * @return array<int, FileItem>
     */
    protected function buildFileItems(array $files, Filesystem $storage, string $disk): array
    {
        $thumbnailDir = config('filament-file-manager.thumbnails.directory', '.thumbnails');

        return array_values(array_filter(
            array_map(function (string $file) use ($storage, $disk, $thumbnailDir): ?FileItem {
                $name = basename($file);

                if (str_starts_with($name, '.') || str_contains($file, $thumbnailDir.'/')) {
                    return null;
                }

                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $category = $this->fileTypeResolver->resolve($name);

                try {
                    $size = $storage->size($file);
                    $lastModified = $storage->lastModified($file);
                } catch (\League\Flysystem\UnableToRetrieveMetadata) {
                    $size = 0;
                    $lastModified = 0;
                }

                $url = $this->getUrl($disk, $file);
                $thumbnailUrl = in_array($extension, FileItem::THUMBNAILABLE_EXTENSIONS, true)
                    ? $this->thumbnailService->getThumbnailUrl($disk, $file)
                    : null;

                return new FileItem(
                    name: $name,
                    path: $file,
                    size: $size,
                    lastModified: $lastModified,
                    extension: $extension,
                    category: $category,
                    mimeType: $this->fileTypeResolver->mimeType($extension),
                    url: $url,
                    thumbnailUrl: $thumbnailUrl,
                );
            }, $files),
        ));
    }

    /**
     * @template T of FileItem|FolderItem
     *
     * @param  array<int, T>  $items
     * @param  \Closure(T, T, SortField): int  $comparator
     * @return array<int, T>
     */
    protected function sortItems(array $items, SortField $field, SortDirection $direction, \Closure $comparator): array
    {
        usort($items, function ($a, $b) use ($field, $direction, $comparator): int {
            $result = $comparator($a, $b, $field);

            return $direction === SortDirection::Desc ? -$result : $result;
        });

        return $items;
    }

    /**
     * @param  array<int, FolderItem>  $folders
     * @return array<int, FolderItem>
     */
    protected function sortFolders(array $folders, SortField $field, SortDirection $direction): array
    {
        return $this->sortItems($folders, $field, $direction, fn (FolderItem $a, FolderItem $b, SortField $f): int => match ($f) {
            SortField::Date => $a->lastModified <=> $b->lastModified,
            default => strnatcasecmp($a->name, $b->name),
        });
    }

    /**
     * @param  array<int, FileItem>  $files
     * @return array<int, FileItem>
     */
    protected function sortFiles(array $files, SortField $field, SortDirection $direction): array
    {
        return $this->sortItems($files, $field, $direction, fn (FileItem $a, FileItem $b, SortField $f): int => match ($f) {
            SortField::Name => strnatcasecmp($a->name, $b->name),
            SortField::Size => $a->size <=> $b->size,
            SortField::Date => $a->lastModified <=> $b->lastModified,
            SortField::Type => strnatcasecmp($a->extension, $b->extension),
        });
    }

    public function clearDirectoryCache(string $disk, string $directory): void
    {
        $this->invalidateCache($disk, $directory);
    }

    /**
     * Invalidate all cached listings for a disk by bumping the cache version.
     * Old cache entries become orphaned and expire naturally.
     */
    public function invalidateDiskCache(string $disk): void
    {
        Cache::increment("fm:{$disk}:cache_version");
    }

    protected function getDiskCacheVersion(string $disk): int
    {
        return (int) Cache::get("fm:{$disk}:cache_version", 0);
    }

    protected function invalidateCache(string $disk, string $directory): void
    {
        $version = $this->getDiskCacheVersion($disk);

        foreach (SortField::cases() as $field) {
            foreach (SortDirection::cases() as $direction) {
                Cache::forget("fm:{$disk}:v{$version}:{$directory}:{$field->value}:{$direction->value}");
            }
        }
    }

    /**
     * @throws \RuntimeException
     */
    protected function disk(string $disk): Filesystem
    {
        $this->ensureLocalDisk($disk);

        return Storage::disk($disk);
    }

    protected function ensureLocalDisk(string $disk): void
    {
        $driver = config("filesystems.disks.{$disk}.driver");

        if ($driver !== 'local') {
            throw new \RuntimeException("The [{$disk}] disk uses the [{$driver}] driver. Remote disks are available in the Pro version of Filament File Manager.");
        }
    }

    protected function hasPublicVisibility(string $disk): bool
    {
        return config("filesystems.disks.{$disk}.visibility") === 'public';
    }
}
