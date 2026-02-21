<?php

namespace MmesDesign\FilamentFileManager\Enums;

enum FileCategory: string
{
    case Image = 'image';
    case Document = 'document';
    case Audio = 'audio';
    case Video = 'video';
    case Archive = 'archive';
    case Code = 'code';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Image => 'Image',
            self::Document => 'Document',
            self::Audio => 'Audio',
            self::Video => 'Video',
            self::Archive => 'Archive',
            self::Code => 'Code',
            self::Other => 'Other',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Image => 'heroicon-o-photo',
            self::Document => 'heroicon-o-document-text',
            self::Audio => 'heroicon-o-musical-note',
            self::Video => 'heroicon-o-film',
            self::Archive => 'heroicon-o-archive-box',
            self::Code => 'heroicon-o-code-bracket',
            self::Other => 'heroicon-o-document',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Image => 'text-purple-500',
            self::Document => 'text-blue-500',
            self::Audio => 'text-green-500',
            self::Video => 'text-red-500',
            self::Archive => 'text-yellow-500',
            self::Code => 'text-gray-500',
            self::Other => 'text-gray-400',
        };
    }
}
