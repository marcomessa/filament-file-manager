<?php

namespace MmesDesign\FilamentFileManager\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use MmesDesign\FilamentFileManager\Services\FileManagerService;
use Tests\TestCase;

class FolderOperationsTest extends TestCase
{
    private FileManagerService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->service = app(FileManagerService::class);
    }

    public function test_create_folder(): void
    {
        $path = $this->service->createFolder('public', '', 'new-folder');

        $this->assertSame('new-folder', $path);
        $this->assertTrue(Storage::disk('public')->directoryExists('new-folder'));
    }

    public function test_create_nested_folder(): void
    {
        Storage::disk('public')->makeDirectory('parent');

        $path = $this->service->createFolder('public', 'parent', 'child');

        $this->assertSame('parent/child', $path);
        $this->assertTrue(Storage::disk('public')->directoryExists('parent/child'));
    }

    public function test_create_duplicate_folder_fails(): void
    {
        Storage::disk('public')->makeDirectory('existing');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('esiste già');

        $this->service->createFolder('public', '', 'existing');
    }

    public function test_create_folder_with_traversal_fails(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->createFolder('public', '../etc', 'hack');
    }

    public function test_create_folder_with_slash_in_name_fails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Filename cannot contain path separators');

        $this->service->createFolder('public', '', 'bad/name');
    }

    public function test_rename_folder(): void
    {
        Storage::disk('public')->makeDirectory('old-folder');

        $newPath = $this->service->rename('public', 'old-folder', 'new-folder');

        $this->assertSame('new-folder', $newPath);
        $this->assertTrue(Storage::disk('public')->directoryExists('new-folder'));
        $this->assertFalse(Storage::disk('public')->directoryExists('old-folder'));
    }

    public function test_delete_folder_with_contents(): void
    {
        Storage::disk('public')->put('folder/a.txt', 'a');
        Storage::disk('public')->put('folder/b.txt', 'b');
        Storage::disk('public')->makeDirectory('folder/sub');

        $this->service->delete('public', 'folder');

        $this->assertFalse(Storage::disk('public')->directoryExists('folder'));
    }
}
