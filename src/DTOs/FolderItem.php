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
}
