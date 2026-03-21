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

    /**
     * @return array<int, string>
     */
    public function extensionsForCategory(FileCategory $category): array
    {
        return array_keys(array_filter(
            self::$extensionMap,
            fn (FileCategory $cat): bool => $cat === $category,
        ));
    }

    public function mimeType(string $extension): string
    {
        $extension = strtolower($extension);

        return match ($extension) {
            // Images
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            'ico' => 'image/x-icon',
            'tiff', 'tif' => 'image/tiff',
            'avif' => 'image/avif',

            // Documents
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'odp' => 'application/vnd.oasis.opendocument.presentation',
            'rtf' => 'application/rtf',

            // Audio
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'flac' => 'audio/flac',
            'aac' => 'audio/aac',
            'ogg' => 'audio/ogg',
            'wma' => 'audio/x-ms-wma',
            'm4a' => 'audio/mp4',
            'aiff' => 'audio/aiff',
            'opus' => 'audio/opus',

            // Video
            'mp4', 'm4v' => 'video/mp4',
            'avi' => 'video/x-msvideo',
            'mkv' => 'video/x-matroska',
            'mov' => 'video/quicktime',
            'wmv' => 'video/x-ms-wmv',
            'flv' => 'video/x-flv',
            'webm' => 'video/webm',

            // Archives
            'zip' => 'application/zip',
            'rar' => 'application/vnd.rar',
            'tar' => 'application/x-tar',
            'gz' => 'application/gzip',
            '7z' => 'application/x-7z-compressed',
            'bz2' => 'application/x-bzip2',
            'xz' => 'application/x-xz',

            // Code / Text
            'json' => 'application/json',
            'xml' => 'application/xml',
            'html' => 'text/html',
            'css' => 'text/css',
            'yaml', 'yml' => 'text/yaml',
            'sql' => 'application/sql',
            'txt', 'csv', 'md' => 'text/plain',

            default => 'application/octet-stream',
        };
    }
}
