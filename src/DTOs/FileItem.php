<?php

namespace MmesDesign\FilamentFileManager\DTOs;

use MmesDesign\FilamentFileManager\Enums\FileCategory;

readonly class FileItem
{
    public const THUMBNAILABLE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'bmp'];

    public function __construct(
        public string $name,
        public string $path,
        public int $size,
        public int $lastModified,
        public string $extension,
        public FileCategory $category,
        public string $mimeType,
        public ?string $url = null,
        public ?string $thumbnailUrl = null,
    ) {}

    public function formattedSize(): string
    {
        if ($this->size < 1024) {
            return $this->size.' B';
        }

        if ($this->size < 1024 * 1024) {
            return round($this->size / 1024, 1).' KB';
        }

        if ($this->size < 1024 * 1024 * 1024) {
            return round($this->size / (1024 * 1024), 1).' MB';
        }

        return round($this->size / (1024 * 1024 * 1024), 1).' GB';
    }

    public function formattedDate(): string
    {
        return date('d M Y H:i', $this->lastModified);
    }

    public function isImage(): bool
    {
        return $this->category === FileCategory::Image;
    }

    public function isThumbnailable(): bool
    {
        return in_array($this->extension, self::THUMBNAILABLE_EXTENSIONS, true);
    }

    public function hasThumbnail(): bool
    {
        return $this->thumbnailUrl !== null;
    }

    /**
     * @return array{name: string, path: string, formattedSize: string, formattedDate: string, extension: string, mimeType: string, category: string, categoryLabel: string, url: ?string, thumbnailUrl: ?string, isImage: bool}
     */
    public function toPreviewArray(): array
    {
        return [
            'name' => $this->name,
            'path' => $this->path,
            'formattedSize' => $this->formattedSize(),
            'formattedDate' => $this->formattedDate(),
            'extension' => $this->extension,
            'mimeType' => $this->mimeType,
            'category' => $this->category->value,
            'categoryLabel' => $this->category->label(),
            'url' => $this->url,
            'thumbnailUrl' => $this->thumbnailUrl,
            'isImage' => $this->isImage(),
        ];
    }
}
