<?php

namespace MmesDesign\FilamentFileManager\DTOs;

use MmesDesign\FilamentFileManager\DTOs\Concerns\HasFormattedDate;

readonly class FolderItem
{
    use HasFormattedDate;

    public function __construct(
        public string $name,
        public string $path,
        public int $lastModified,
    ) {}

    /**
     * @return array{name: string, path: string, lastModified: int}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'path' => $this->path,
            'lastModified' => $this->lastModified,
        ];
    }

    /**
     * @param  array{name: string, path: string, lastModified: int}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            path: $data['path'],
            lastModified: $data['lastModified'],
        );
    }
}
