<?php

namespace MmesDesign\FilamentFileManager\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use MmesDesign\FilamentFileManager\DTOs\DirectoryListing;
use MmesDesign\FilamentFileManager\Enums\FileCategory;
use MmesDesign\FilamentFileManager\Enums\SortDirection;
use MmesDesign\FilamentFileManager\Enums\SortField;
use MmesDesign\FilamentFileManager\Services\FileManagerService;
use Tests\TestCase;

class FileManagerServiceTest extends TestCase
{
    private FileManagerService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->service = app(FileManagerService::class);
    }

    public function test_list_empty_directory(): void
    {
        $listing = $this->service->listDirectory('public');

        $this->assertInstanceOf(DirectoryListing::class, $listing);
        $this->assertSame('', $listing->path);
        $this->assertSame('public', $listing->disk);
        $this->assertCount(0, $listing->folders);
        $this->assertCount(0, $listing->files);
        $this->assertTrue($listing->isEmpty());
    }

    public function test_list_directory_with_files(): void
    {
        Storage::disk('public')->put('photo.jpg', 'image content');
        Storage::disk('public')->put('document.pdf', 'pdf content');

        $listing = $this->service->listDirectory('public');

        $this->assertCount(0, $listing->folders);
        $this->assertCount(2, $listing->files);
        $this->assertFalse($listing->isEmpty());
        $this->assertSame(2, $listing->totalCount());
    }

    public function test_list_directory_with_folders(): void
    {
        Storage::disk('public')->makeDirectory('images');
        Storage::disk('public')->makeDirectory('documents');
        Storage::disk('public')->put('file.txt', 'text');

        $listing = $this->service->listDirectory('public');

        $this->assertCount(2, $listing->folders);
        $this->assertCount(1, $listing->files);
    }

    public function test_list_subdirectory(): void
    {
        Storage::disk('public')->put('images/photo.jpg', 'image');
        Storage::disk('public')->put('images/banner.png', 'banner');
        Storage::disk('public')->put('root.txt', 'root file');

        $listing = $this->service->listDirectory('public', 'images');

        $this->assertSame('images', $listing->path);
        $this->assertCount(2, $listing->files);
    }

    public function test_file_items_have_correct_properties(): void
    {
        Storage::disk('public')->put('photo.jpg', 'image content');

        $listing = $this->service->listDirectory('public');
        $file = $listing->files[0];

        $this->assertSame('photo.jpg', $file->name);
        $this->assertSame('photo.jpg', $file->path);
        $this->assertSame('jpg', $file->extension);
        $this->assertSame(FileCategory::Image, $file->category);
        $this->assertSame('image/jpeg', $file->mimeType);
        $this->assertGreaterThan(0, $file->size);
        $this->assertGreaterThan(0, $file->lastModified);
        $this->assertNotNull($file->url);
        $this->assertStringContainsString('photo.jpg', $file->url);
    }

    public function test_file_items_have_url_populated(): void
    {
        Storage::disk('public')->put('document.pdf', 'pdf content');

        $listing = $this->service->listDirectory('public');
        $file = $listing->files[0];

        $this->assertNotNull($file->url);
        $this->assertStringContainsString('document.pdf', $file->url);
    }

    public function test_thumbnail_url_is_null_for_non_image_files(): void
    {
        Storage::disk('public')->put('document.pdf', 'pdf content');

        $listing = $this->service->listDirectory('public');
        $file = $listing->files[0];

        $this->assertNull($file->thumbnailUrl);
    }

    public function test_thumbnail_url_is_null_when_no_thumbnail_generated(): void
    {
        Storage::disk('public')->put('photo.jpg', 'image content');

        $listing = $this->service->listDirectory('public');
        $file = $listing->files[0];

        $this->assertNull($file->thumbnailUrl);
        $this->assertTrue($file->isThumbnailable());
        $this->assertFalse($file->hasThumbnail());
    }

    public function test_thumbnail_url_populated_when_thumbnail_exists(): void
    {
        Storage::disk('public')->put('photo.jpg', 'image content');
        Storage::disk('public')->put('.thumbnails/photo.jpg', 'thumb content');

        $listing = $this->service->listDirectory('public');
        $file = $listing->files[0];

        $this->assertNotNull($file->thumbnailUrl);
        $this->assertTrue($file->hasThumbnail());
    }

    public function test_rename_deletes_old_thumbnail(): void
    {
        Storage::disk('public')->put('photo.jpg', 'image');
        Storage::disk('public')->put('.thumbnails/photo.jpg', 'thumb');

        $this->service->rename('public', 'photo.jpg', 'renamed.jpg');

        Storage::disk('public')->assertMissing('.thumbnails/photo.jpg');
    }

    public function test_delete_file_deletes_thumbnail(): void
    {
        Storage::disk('public')->put('photo.jpg', 'image');
        Storage::disk('public')->put('.thumbnails/photo.jpg', 'thumb');

        $this->service->delete('public', 'photo.jpg');

        Storage::disk('public')->assertMissing('.thumbnails/photo.jpg');
    }

    public function test_move_deletes_old_thumbnail(): void
    {
        Storage::disk('public')->makeDirectory('target');
        Storage::disk('public')->put('photo.jpg', 'image');
        Storage::disk('public')->put('.thumbnails/photo.jpg', 'thumb');

        $this->service->move('public', 'photo.jpg', 'target');

        Storage::disk('public')->assertMissing('.thumbnails/photo.jpg');
    }

    public function test_folder_items_have_correct_properties(): void
    {
        Storage::disk('public')->makeDirectory('photos');

        $listing = $this->service->listDirectory('public');
        $folder = $listing->folders[0];

        $this->assertSame('photos', $folder->name);
        $this->assertSame('photos', $folder->path);
    }

    public function test_hidden_files_are_excluded(): void
    {
        Storage::disk('public')->put('.hidden', 'hidden file');
        Storage::disk('public')->put('visible.txt', 'visible file');

        $listing = $this->service->listDirectory('public');

        $this->assertCount(1, $listing->files);
        $this->assertSame('visible.txt', $listing->files[0]->name);
    }

    public function test_thumbnail_directory_files_are_excluded(): void
    {
        Storage::disk('public')->put('.thumbnails/photo_thumb.jpg', 'thumb');
        Storage::disk('public')->put('photo.jpg', 'image');

        $listing = $this->service->listDirectory('public');

        $this->assertCount(1, $listing->files);
        $this->assertSame('photo.jpg', $listing->files[0]->name);
    }

    public function test_sort_files_by_name_ascending(): void
    {
        Storage::disk('public')->put('charlie.txt', 'c');
        Storage::disk('public')->put('alpha.txt', 'a');
        Storage::disk('public')->put('bravo.txt', 'b');

        $listing = $this->service->listDirectory('public', '', SortField::Name, SortDirection::Asc);

        $this->assertSame('alpha.txt', $listing->files[0]->name);
        $this->assertSame('bravo.txt', $listing->files[1]->name);
        $this->assertSame('charlie.txt', $listing->files[2]->name);
    }

    public function test_sort_files_by_name_descending(): void
    {
        Storage::disk('public')->put('alpha.txt', 'a');
        Storage::disk('public')->put('charlie.txt', 'c');
        Storage::disk('public')->put('bravo.txt', 'b');

        $listing = $this->service->listDirectory('public', '', SortField::Name, SortDirection::Desc);

        $this->assertSame('charlie.txt', $listing->files[0]->name);
        $this->assertSame('bravo.txt', $listing->files[1]->name);
        $this->assertSame('alpha.txt', $listing->files[2]->name);
    }

    public function test_sort_files_by_size(): void
    {
        Storage::disk('public')->put('small.txt', 'a');
        Storage::disk('public')->put('large.txt', str_repeat('x', 1000));
        Storage::disk('public')->put('medium.txt', str_repeat('x', 100));

        $listing = $this->service->listDirectory('public', '', SortField::Size, SortDirection::Asc);

        $this->assertSame('small.txt', $listing->files[0]->name);
        $this->assertSame('medium.txt', $listing->files[1]->name);
        $this->assertSame('large.txt', $listing->files[2]->name);
    }

    public function test_sort_files_by_type(): void
    {
        Storage::disk('public')->put('data.csv', 'csv');
        Storage::disk('public')->put('image.png', 'png');
        Storage::disk('public')->put('readme.md', 'md');

        $listing = $this->service->listDirectory('public', '', SortField::Type, SortDirection::Asc);

        $this->assertSame('data.csv', $listing->files[0]->name);
        $this->assertSame('readme.md', $listing->files[1]->name);
        $this->assertSame('image.png', $listing->files[2]->name);
    }

    public function test_directory_traversal_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->listDirectory('public', '../../../etc');
    }

    public function test_exists_returns_true_for_existing_file(): void
    {
        Storage::disk('public')->put('test.txt', 'content');

        $this->assertTrue($this->service->exists('public', 'test.txt'));
    }

    public function test_exists_returns_false_for_missing_file(): void
    {
        $this->assertFalse($this->service->exists('public', 'nonexistent.txt'));
    }

    public function test_get_url_returns_url_for_public_disk(): void
    {
        Storage::disk('public')->put('test.txt', 'content');

        $url = $this->service->getUrl('public', 'test.txt');

        $this->assertNotNull($url);
        $this->assertStringContainsString('test.txt', $url);
    }

    public function test_move_bulk(): void
    {
        Storage::disk('public')->put('file1.txt', 'content1');
        Storage::disk('public')->put('file2.txt', 'content2');
        Storage::disk('public')->put('file3.txt', 'content3');
        Storage::disk('public')->makeDirectory('target');

        $count = $this->service->moveBulk('public', ['file1.txt', 'file2.txt', 'file3.txt'], 'target');

        $this->assertSame(3, $count);
        Storage::disk('public')->assertExists('target/file1.txt');
        Storage::disk('public')->assertExists('target/file2.txt');
        Storage::disk('public')->assertExists('target/file3.txt');
        Storage::disk('public')->assertMissing('file1.txt');
        Storage::disk('public')->assertMissing('file2.txt');
        Storage::disk('public')->assertMissing('file3.txt');
    }

    public function test_move_bulk_to_root(): void
    {
        Storage::disk('public')->put('subdir/file1.txt', 'content1');
        Storage::disk('public')->put('subdir/file2.txt', 'content2');

        $count = $this->service->moveBulk('public', ['subdir/file1.txt', 'subdir/file2.txt'], '');

        $this->assertSame(2, $count);
        Storage::disk('public')->assertExists('file1.txt');
        Storage::disk('public')->assertExists('file2.txt');
    }

    public function test_remote_disk_is_blocked(): void
    {
        if ($this->service instanceof \MmesDesign\FilamentFileManagerPro\Services\FileManagerService) {
            $this->markTestSkipped('Pro version allows remote disks.');
        }

        config()->set('filesystems.disks.s3test', [
            'driver' => 's3',
            'key' => 'test',
            'secret' => 'test',
            'region' => 'us-east-1',
            'bucket' => 'test',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Remote disks are available in the Pro version');

        $this->service->listDirectory('s3test');
    }
}
