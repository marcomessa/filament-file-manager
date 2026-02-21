<?php

namespace MmesDesign\FilamentFileManager\Tests\Unit;

use MmesDesign\FilamentFileManager\Enums\FileCategory;
use MmesDesign\FilamentFileManager\Services\FileTypeResolver;
use PHPUnit\Framework\TestCase;

class FileTypeResolverTest extends TestCase
{
    private FileTypeResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new FileTypeResolver;
    }

    public function test_resolve_image_extensions(): void
    {
        $this->assertSame(FileCategory::Image, $this->resolver->resolve('photo.jpg'));
        $this->assertSame(FileCategory::Image, $this->resolver->resolve('image.PNG'));
        $this->assertSame(FileCategory::Image, $this->resolver->resolve('graphic.svg'));
        $this->assertSame(FileCategory::Image, $this->resolver->resolve('pic.webp'));
    }

    public function test_resolve_document_extensions(): void
    {
        $this->assertSame(FileCategory::Document, $this->resolver->resolve('report.pdf'));
        $this->assertSame(FileCategory::Document, $this->resolver->resolve('sheet.xlsx'));
        $this->assertSame(FileCategory::Document, $this->resolver->resolve('notes.txt'));
    }

    public function test_resolve_audio_extensions(): void
    {
        $this->assertSame(FileCategory::Audio, $this->resolver->resolve('song.mp3'));
        $this->assertSame(FileCategory::Audio, $this->resolver->resolve('track.flac'));
        $this->assertSame(FileCategory::Audio, $this->resolver->resolve('audio.wav'));
    }

    public function test_resolve_video_extensions(): void
    {
        $this->assertSame(FileCategory::Video, $this->resolver->resolve('movie.mp4'));
        $this->assertSame(FileCategory::Video, $this->resolver->resolve('clip.mkv'));
    }

    public function test_resolve_archive_extensions(): void
    {
        $this->assertSame(FileCategory::Archive, $this->resolver->resolve('backup.zip'));
        $this->assertSame(FileCategory::Archive, $this->resolver->resolve('archive.tar'));
    }

    public function test_resolve_code_extensions(): void
    {
        $this->assertSame(FileCategory::Code, $this->resolver->resolve('config.json'));
        $this->assertSame(FileCategory::Code, $this->resolver->resolve('style.css'));
    }

    public function test_resolve_unknown_extension_returns_other(): void
    {
        $this->assertSame(FileCategory::Other, $this->resolver->resolve('data.xyz'));
        $this->assertSame(FileCategory::Other, $this->resolver->resolve('noextension'));
    }

    public function test_icon_returns_heroicon(): void
    {
        $this->assertSame('heroicon-o-photo', $this->resolver->icon('image.jpg'));
        $this->assertSame('heroicon-o-document-text', $this->resolver->icon('report.pdf'));
        $this->assertSame('heroicon-o-musical-note', $this->resolver->icon('song.mp3'));
    }

    public function test_mime_type_resolution(): void
    {
        $this->assertSame('image/jpeg', $this->resolver->mimeType('jpg'));
        $this->assertSame('application/pdf', $this->resolver->mimeType('pdf'));
        $this->assertSame('audio/mpeg', $this->resolver->mimeType('mp3'));
        $this->assertSame('application/octet-stream', $this->resolver->mimeType('xyz'));
    }
}
