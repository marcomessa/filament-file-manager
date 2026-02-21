<?php

namespace MmesDesign\FilamentFileManager\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use MmesDesign\FilamentFileManager\Services\ThumbnailService;
use Tests\TestCase;

class ThumbnailServiceTest extends TestCase
{
    private ThumbnailService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->service = app(ThumbnailService::class);
    }

    public function test_thumbnail_path_prepends_directory(): void
    {
        $this->assertSame('.thumbnails/photos/image.jpg', $this->service->thumbnailPath('photos/image.jpg'));
        $this->assertSame('.thumbnails/image.jpg', $this->service->thumbnailPath('image.jpg'));
    }

    public function test_generate_creates_thumbnail_for_jpg(): void
    {
        $image = $this->createTestImage(400, 300, 'jpg');
        Storage::disk('public')->put('photo.jpg', $image);

        $result = $this->service->generate('public', 'photo.jpg');

        $this->assertTrue($result);
        Storage::disk('public')->assertExists('.thumbnails/photo.jpg');
    }

    public function test_generate_creates_thumbnail_for_png(): void
    {
        $image = $this->createTestImage(400, 300, 'png');
        Storage::disk('public')->put('photo.png', $image);

        $result = $this->service->generate('public', 'photo.png');

        $this->assertTrue($result);
        Storage::disk('public')->assertExists('.thumbnails/photo.png');
    }

    public function test_generate_returns_false_for_nonexistent_file(): void
    {
        $result = $this->service->generate('public', 'nonexistent.jpg');

        $this->assertFalse($result);
    }

    public function test_generate_returns_false_for_corrupt_image(): void
    {
        Storage::disk('public')->put('corrupt.jpg', 'not a real image');

        $result = $this->service->generate('public', 'corrupt.jpg');

        $this->assertFalse($result);
        Storage::disk('public')->assertMissing('.thumbnails/corrupt.jpg');
    }

    public function test_generate_returns_false_for_non_thumbnailable_extension(): void
    {
        Storage::disk('public')->put('document.pdf', 'pdf content');

        $result = $this->service->generate('public', 'document.pdf');

        $this->assertFalse($result);
    }

    public function test_generate_respects_configured_dimensions(): void
    {
        config(['filament-file-manager.thumbnails.width' => 100, 'filament-file-manager.thumbnails.height' => 100]);

        $image = $this->createTestImage(800, 600, 'jpg');
        Storage::disk('public')->put('large.jpg', $image);

        $result = $this->service->generate('public', 'large.jpg');

        $this->assertTrue($result);
        Storage::disk('public')->assertExists('.thumbnails/large.jpg');

        $thumbnailContents = Storage::disk('public')->get('.thumbnails/large.jpg');
        $size = getimagesizefromstring($thumbnailContents);
        $this->assertSame(100, $size[0]);
        $this->assertSame(100, $size[1]);
    }

    public function test_get_thumbnail_url_generates_and_returns_url(): void
    {
        $image = $this->createTestImage(400, 300, 'jpg');
        Storage::disk('public')->put('photo.jpg', $image);

        $url = $this->service->getThumbnailUrl('public', 'photo.jpg');

        $this->assertNotNull($url);
        $this->assertStringContainsString('.thumbnails/photo.jpg', $url);
        Storage::disk('public')->assertExists('.thumbnails/photo.jpg');
    }

    public function test_get_thumbnail_url_returns_null_when_disabled(): void
    {
        config(['filament-file-manager.thumbnails.enabled' => false]);

        $image = $this->createTestImage(400, 300, 'jpg');
        Storage::disk('public')->put('photo.jpg', $image);

        $url = $this->service->getThumbnailUrl('public', 'photo.jpg');

        $this->assertNull($url);
        Storage::disk('public')->assertMissing('.thumbnails/photo.jpg');
    }

    public function test_get_existing_thumbnail_url_returns_null_when_no_thumbnail(): void
    {
        Storage::disk('public')->put('photo.jpg', 'image');

        $url = $this->service->getExistingThumbnailUrl('public', 'photo.jpg');

        $this->assertNull($url);
    }

    public function test_get_existing_thumbnail_url_returns_url_when_thumbnail_exists(): void
    {
        $image = $this->createTestImage(200, 200, 'jpg');
        Storage::disk('public')->put('.thumbnails/photo.jpg', $image);

        $url = $this->service->getExistingThumbnailUrl('public', 'photo.jpg');

        $this->assertNotNull($url);
        $this->assertStringContainsString('.thumbnails/photo.jpg', $url);
    }

    public function test_delete_removes_thumbnail(): void
    {
        Storage::disk('public')->put('.thumbnails/photo.jpg', 'thumb');

        $this->service->delete('public', 'photo.jpg');

        Storage::disk('public')->assertMissing('.thumbnails/photo.jpg');
    }

    public function test_delete_does_nothing_when_no_thumbnail(): void
    {
        $this->service->delete('public', 'nonexistent.jpg');

        $this->assertTrue(true);
    }

    public function test_thumbnails_not_generated_when_disabled(): void
    {
        config(['filament-file-manager.thumbnails.enabled' => false]);

        $image = $this->createTestImage(400, 300, 'jpg');
        Storage::disk('public')->put('photo.jpg', $image);

        $result = $this->service->generate('public', 'photo.jpg');

        $this->assertFalse($result);
        Storage::disk('public')->assertMissing('.thumbnails/photo.jpg');
    }

    public function test_generate_handles_nested_path(): void
    {
        $image = $this->createTestImage(400, 300, 'jpg');
        Storage::disk('public')->put('photos/vacation/beach.jpg', $image);

        $result = $this->service->generate('public', 'photos/vacation/beach.jpg');

        $this->assertTrue($result);
        Storage::disk('public')->assertExists('.thumbnails/photos/vacation/beach.jpg');
    }

    /**
     * Create a real test image and return its binary content.
     */
    private function createTestImage(int $width, int $height, string $format): string
    {
        $image = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
        imagefill($image, 0, 0, $color);

        ob_start();

        match ($format) {
            'png' => imagepng($image),
            'gif' => imagegif($image),
            'webp' => imagewebp($image),
            default => imagejpeg($image, null, 90),
        };

        $content = ob_get_clean();
        imagedestroy($image);

        return $content;
    }
}
