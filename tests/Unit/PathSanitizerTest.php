<?php

namespace MmesDesign\FilamentFileManager\Tests\Unit;

use MmesDesign\FilamentFileManager\Support\PathSanitizer;
use PHPUnit\Framework\TestCase;

class PathSanitizerTest extends TestCase
{
    private PathSanitizer $sanitizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sanitizer = new PathSanitizer;
    }

    public function test_sanitize_empty_path(): void
    {
        $this->assertSame('', $this->sanitizer->sanitize(''));
    }

    public function test_sanitize_simple_path(): void
    {
        $this->assertSame('images/photos', $this->sanitizer->sanitize('images/photos'));
    }

    public function test_sanitize_strips_dot_segments(): void
    {
        $this->assertSame('images/photos', $this->sanitizer->sanitize('./images/./photos'));
    }

    public function test_sanitize_strips_empty_segments(): void
    {
        $this->assertSame('images/photos', $this->sanitizer->sanitize('images//photos'));
    }

    public function test_sanitize_normalizes_backslashes(): void
    {
        $this->assertSame('images/photos', $this->sanitizer->sanitize('images\\photos'));
    }

    public function test_sanitize_rejects_directory_traversal(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Directory traversal is not allowed.');

        $this->sanitizer->sanitize('images/../../../etc/passwd');
    }

    public function test_sanitize_rejects_absolute_path(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Absolute paths are not allowed.');

        $this->sanitizer->sanitize('/etc/passwd');
    }

    public function test_sanitize_rejects_backslash_absolute_path(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Absolute paths are not allowed.');

        $this->sanitizer->sanitize('\\Windows\\System32');
    }

    public function test_sanitize_strips_null_bytes(): void
    {
        $this->assertSame('images/photo.jpg', $this->sanitizer->sanitize("images/photo.jpg\0"));
    }

    public function test_join_directory_and_filename(): void
    {
        $this->assertSame('images/photo.jpg', $this->sanitizer->join('images', 'photo.jpg'));
    }

    public function test_join_empty_directory(): void
    {
        $this->assertSame('photo.jpg', $this->sanitizer->join('', 'photo.jpg'));
    }

    public function test_join_rejects_filename_with_path_separator(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Filename cannot contain path separators.');

        $this->sanitizer->join('images', 'sub/photo.jpg');
    }
}
