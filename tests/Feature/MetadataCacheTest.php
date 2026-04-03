<?php

namespace MmesDesign\FilamentFileManager\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use MmesDesign\FilamentFileManager\Services\FileManagerService;
use Tests\TestCase;

class MetadataCacheTest extends TestCase
{
    private FileManagerService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Cache::flush();
        $this->service = app(FileManagerService::class);
    }

    public function test_listing_is_cached(): void
    {
        Storage::disk('public')->put('file.txt', 'content');

        $listing1 = $this->service->listDirectory('public');
        $listing2 = $this->service->listDirectory('public');

        $this->assertEquals($listing1, $listing2);
        $this->assertTrue(Cache::has('fml:public:v0::name:asc'));
    }

    public function test_cache_key_includes_sort_parameters(): void
    {
        Storage::disk('public')->put('file.txt', 'content');

        $this->service->listDirectory('public');
        $this->assertTrue(Cache::has('fml:public:v0::name:asc'));
        $this->assertFalse(Cache::has('fml:public:v0::size:desc'));

        $this->service->listDirectory(
            'public',
            '',
            \MmesDesign\FilamentFileManager\Enums\SortField::Size,
            \MmesDesign\FilamentFileManager\Enums\SortDirection::Desc,
        );
        $this->assertTrue(Cache::has('fml:public:v0::size:desc'));
    }

    public function test_cache_key_includes_path(): void
    {
        Storage::disk('public')->put('subdir/file.txt', 'content');

        $this->service->listDirectory('public', 'subdir');

        $this->assertTrue(Cache::has('fml:public:v0:subdir:name:asc'));
        $this->assertFalse(Cache::has('fml:public:v0::name:asc'));
    }

    public function test_upload_invalidates_cache(): void
    {
        Storage::disk('public')->put('existing.txt', 'content');
        $this->service->listDirectory('public');
        $this->assertTrue(Cache::has('fml:public:v0::name:asc'));

        $file = UploadedFile::fake()->create('new.txt', 100);
        $this->service->upload('public', '', $file);

        $this->assertFalse(Cache::has('fml:public:v0::name:asc'));
    }

    public function test_delete_invalidates_cache(): void
    {
        Storage::disk('public')->put('file.txt', 'content');
        $this->service->listDirectory('public');
        $this->assertTrue(Cache::has('fml:public:v0::name:asc'));

        $this->service->delete('public', 'file.txt');

        $this->assertFalse(Cache::has('fml:public:v0::name:asc'));
    }

    public function test_rename_invalidates_cache(): void
    {
        Storage::disk('public')->put('old.txt', 'content');
        $this->service->listDirectory('public');
        $this->assertTrue(Cache::has('fml:public:v0::name:asc'));

        $this->service->rename('public', 'old.txt', 'new.txt');

        $this->assertFalse(Cache::has('fml:public:v0::name:asc'));
    }

    public function test_create_folder_invalidates_cache(): void
    {
        $this->service->listDirectory('public');
        $this->assertTrue(Cache::has('fml:public:v0::name:asc'));

        $this->service->createFolder('public', '', 'newfolder');

        $this->assertFalse(Cache::has('fml:public:v0::name:asc'));
    }

    public function test_move_invalidates_both_source_and_destination_cache(): void
    {
        Storage::disk('public')->put('file.txt', 'content');
        Storage::disk('public')->makeDirectory('target');

        $this->service->listDirectory('public');
        $this->service->listDirectory('public', 'target');

        $this->assertTrue(Cache::has('fml:public:v0::name:asc'));
        $this->assertTrue(Cache::has('fml:public:v0:target:name:asc'));

        $this->service->move('public', 'file.txt', 'target');

        $this->assertFalse(Cache::has('fml:public:v0::name:asc'));
        $this->assertFalse(Cache::has('fml:public:v0:target:name:asc'));
    }

    public function test_invalidation_clears_all_sort_combinations(): void
    {
        Storage::disk('public')->put('file.txt', 'content');

        // Populate cache with multiple sort combos
        $this->service->listDirectory('public');
        $this->service->listDirectory('public', '', \MmesDesign\FilamentFileManager\Enums\SortField::Size);
        $this->service->listDirectory('public', '', \MmesDesign\FilamentFileManager\Enums\SortField::Date);

        $this->assertTrue(Cache::has('fml:public:v0::name:asc'));
        $this->assertTrue(Cache::has('fml:public:v0::size:asc'));
        $this->assertTrue(Cache::has('fml:public:v0::date:asc'));

        $this->service->delete('public', 'file.txt');

        $this->assertFalse(Cache::has('fml:public:v0::name:asc'));
        $this->assertFalse(Cache::has('fml:public:v0::size:asc'));
        $this->assertFalse(Cache::has('fml:public:v0::date:asc'));
    }
}
