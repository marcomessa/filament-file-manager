<?php

namespace MmesDesign\FilamentFileManager\DTOs;

readonly class DirectoryListing
{
    /**
     * @param  array<int, FolderItem>  $folders
     * @param  array<int, FileItem>  $files
     */
    public function __construct(
        public string $path,
        public string $disk,
        public array $folders,
        public array $files,
    ) {}

    /**
     * @return array{path: string, disk: string, folders: array<int, array<string, mixed>>, files: array<int, array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'disk' => $this->disk,
            'folders' => array_map(fn (FolderItem $f): array => $f->toArray(), $this->folders),
            'files' => array_map(fn (FileItem $f): array => $f->toArray(), $this->files),
        ];
    }

    /**
     * @param  array{path: string, disk: string, folders: array<int, array<string, mixed>>, files: array<int, array<string, mixed>>}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            path: $data['path'],
            disk: $data['disk'],
            folders: array_map(fn (array $f): FolderItem => FolderItem::fromArray($f), $data['folders']),
            files: array_map(fn (array $f): FileItem => FileItem::fromArray($f), $data['files']),
        );
    }

    public function isEmpty(): bool
    {
        return count($this->folders) === 0 && count($this->files) === 0;
    }

    public function totalCount(): int
    {
        return count($this->folders) + count($this->files);
    }

    /**
     * @param  callable(FolderItem): bool  $callback
     */
    public function filterFolders(callable $callback): self
    {
        return new self(
            path: $this->path,
            disk: $this->disk,
            folders: array_values(array_filter($this->folders, $callback)),
            files: $this->files,
        );
    }

    /**
     * @param  callable(FileItem): bool  $callback
     */
    public function filterFiles(callable $callback): self
    {
        return new self(
            path: $this->path,
            disk: $this->disk,
            folders: $this->folders,
            files: array_values(array_filter($this->files, $callback)),
        );
    }
}
