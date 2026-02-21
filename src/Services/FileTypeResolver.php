<?php

namespace MmesDesign\FilamentFileManager\Services;

use MmesDesign\FilamentFileManager\Enums\FileCategory;

class FileTypeResolver
{
    /**
     * @var array<string, FileCategory>
     */
    protected static array $extensionMap = [
        // Images
        'jpg' => FileCategory::Image,
        'jpeg' => FileCategory::Image,
        'png' => FileCategory::Image,
        'gif' => FileCategory::Image,
        'svg' => FileCategory::Image,
        'webp' => FileCategory::Image,
        'bmp' => FileCategory::Image,
        'ico' => FileCategory::Image,
        'tiff' => FileCategory::Image,
        'tif' => FileCategory::Image,
        'avif' => FileCategory::Image,

        // Documents
        'pdf' => FileCategory::Document,
        'doc' => FileCategory::Document,
        'docx' => FileCategory::Document,
        'xls' => FileCategory::Document,
        'xlsx' => FileCategory::Document,
        'ppt' => FileCategory::Document,
        'pptx' => FileCategory::Document,
        'odt' => FileCategory::Document,
        'ods' => FileCategory::Document,
        'odp' => FileCategory::Document,
        'txt' => FileCategory::Document,
        'rtf' => FileCategory::Document,
        'csv' => FileCategory::Document,

        // Audio
        'mp3' => FileCategory::Audio,
        'wav' => FileCategory::Audio,
        'flac' => FileCategory::Audio,
        'aac' => FileCategory::Audio,
        'ogg' => FileCategory::Audio,
        'wma' => FileCategory::Audio,
        'm4a' => FileCategory::Audio,
        'aiff' => FileCategory::Audio,
        'opus' => FileCategory::Audio,

        // Video
        'mp4' => FileCategory::Video,
        'avi' => FileCategory::Video,
        'mkv' => FileCategory::Video,
        'mov' => FileCategory::Video,
        'wmv' => FileCategory::Video,
        'flv' => FileCategory::Video,
        'webm' => FileCategory::Video,
        'm4v' => FileCategory::Video,

        // Archives
        'zip' => FileCategory::Archive,
        'rar' => FileCategory::Archive,
        'tar' => FileCategory::Archive,
        'gz' => FileCategory::Archive,
        '7z' => FileCategory::Archive,
        'bz2' => FileCategory::Archive,
        'xz' => FileCategory::Archive,

        // Code
        'html' => FileCategory::Code,
        'css' => FileCategory::Code,
        'json' => FileCategory::Code,
        'xml' => FileCategory::Code,
        'yaml' => FileCategory::Code,
        'yml' => FileCategory::Code,
        'md' => FileCategory::Code,
        'sql' => FileCategory::Code,
    ];

    public function resolve(string $filename): FileCategory
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return self::$extensionMap[$extension] ?? FileCategory::Other;
    }

    public function icon(string $filename): string
    {
        return $this->resolve($filename)->icon();
    }

    public function color(string $filename): string
    {
        return $this->resolve($filename)->color();
    }

    public function mimeType(string $extension): string
    {
        $extension = strtolower($extension);

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'mp4' => 'video/mp4',
            'zip' => 'application/zip',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'txt', 'csv', 'md' => 'text/plain',
            default => 'application/octet-stream',
        };
    }
}
