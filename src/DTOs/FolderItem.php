<?php

namespace MmesDesign\FilamentFileManager\DTOs;

readonly class FolderItem
{
    public function __construct(
        public string $name,
        public string $path,
        public int $lastModified,
    ) {}

    public function formattedDate(): string
    {
        return date('d M Y H:i', $this->lastModified);
    }
}
