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

    public function isEmpty(): bool
    {
        return count($this->folders) === 0 && count($this->files) === 0;
    }

    public function totalCount(): int
    {
        return count($this->folders) + count($this->files);
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
