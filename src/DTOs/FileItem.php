<?php

namespace MmesDesign\FilamentFileManager\DTOs;

use MmesDesign\FilamentFileManager\DTOs\Concerns\HasFormattedDate;
use MmesDesign\FilamentFileManager\Enums\FileCategory;

readonly class FileItem
{
    use HasFormattedDate;
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

    /**
     * @return array{name: string, path: string, size: int, lastModified: int, extension: string, category: string, mimeType: string, url: ?string, thumbnailUrl: ?string}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'path' => $this->path,
            'size' => $this->size,
            'lastModified' => $this->lastModified,
            'extension' => $this->extension,
            'category' => $this->category->value,
            'mimeType' => $this->mimeType,
            'url' => $this->url,
            'thumbnailUrl' => $this->thumbnailUrl,
        ];
    }

    /**
     * @param  array{name: string, path: string, size: int, lastModified: int, extension: string, category: string, mimeType: string, url: ?string, thumbnailUrl: ?string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            path: $data['path'],
            size: $data['size'],
            lastModified: $data['lastModified'],
            extension: $data['extension'],
            category: FileCategory::from($data['category']),
            mimeType: $data['mimeType'],
            url: $data['url'] ?? null,
            thumbnailUrl: $data['thumbnailUrl'] ?? null,
        );
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
