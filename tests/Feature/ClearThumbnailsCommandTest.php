<?php

namespace MmesDesign\FilamentFileManager\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use MmesDesign\FilamentFileManager\Services\FileManagerService;
use Tests\TestCase;

class ClearThumbnailsCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_clears_thumbnails_directory(): void
    {
        Storage::disk('public')->put('.thumbnails/photo.jpg', 'thumb');
        Storage::disk('public')->put('.thumbnails/nested/image.png', 'thumb');

        $this->artisan('filament-file-manager:clear-thumbnails', ['--disk' => 'public'])
            ->assertSuccessful();

        Storage::disk('public')->assertMissing('.thumbnails/photo.jpg');
        Storage::disk('public')->assertMissing('.thumbnails/nested/image.png');
        $this->assertFalse(Storage::disk('public')->directoryExists('.thumbnails'));
    }

    public function test_succeeds_when_no_thumbnails_directory_exists(): void
    {
        $this->artisan('filament-file-manager:clear-thumbnails', ['--disk' => 'public'])
            ->assertSuccessful();
    }

    public function test_uses_default_disk_from_config(): void
    {
        config(['filament-file-manager.disk' => 'public']);

        Storage::disk('public')->put('.thumbnails/photo.jpg', 'thumb');

        $this->artisan('filament-file-manager:clear-thumbnails')
            ->assertSuccessful();

        Storage::disk('public')->assertMissing('.thumbnails/photo.jpg');
    }

    public function test_respects_configured_thumbnails_directory(): void
    {
        config(['filament-file-manager.thumbnails.directory' => '.thumbs']);

        Storage::disk('public')->put('.thumbs/photo.jpg', 'thumb');

        $this->artisan('filament-file-manager:clear-thumbnails', ['--disk' => 'public'])
            ->assertSuccessful();

        Storage::disk('public')->assertMissing('.thumbs/photo.jpg');
    }

    public function test_fails_when_service_rejects_disk(): void
    {
        $mock = $this->partialMock(FileManagerService::class, function ($mock) {
            $mock->shouldReceive('directoryExists')
                ->andThrow(new \RuntimeException('Remote disks are not supported.'));
        });

        $this->artisan('filament-file-manager:clear-thumbnails', ['--disk' => 'remote'])
            ->assertFailed();
    }

    public function test_does_not_delete_regular_files(): void
    {
        Storage::disk('public')->put('photo.jpg', 'original');
        Storage::disk('public')->put('.thumbnails/photo.jpg', 'thumb');

        $this->artisan('filament-file-manager:clear-thumbnails', ['--disk' => 'public'])
            ->assertSuccessful();

        Storage::disk('public')->assertExists('photo.jpg');
        Storage::disk('public')->assertMissing('.thumbnails/photo.jpg');
    }
}
