<?php

namespace MmesDesign\FilamentFileManager\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MmesDesign\FilamentFileManager\Services\FileManagerService;
use Tests\TestCase;

class FileOperationsTest extends TestCase
{
    private FileManagerService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->service = app(FileManagerService::class);
    }

    public function test_upload_file(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg');

        $path = $this->service->upload('public', '', $file);

        $this->assertSame('photo.jpg', $path);
        Storage::disk('public')->assertExists('photo.jpg');
    }

    public function test_upload_file_to_subdirectory(): void
    {
        Storage::disk('public')->makeDirectory('images');
        $file = UploadedFile::fake()->image('photo.jpg');

        $path = $this->service->upload('public', 'images', $file);

        $this->assertSame('images/photo.jpg', $path);
        Storage::disk('public')->assertExists('images/photo.jpg');
    }

    public function test_upload_file_with_custom_filename(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg');

        $path = $this->service->upload('public', '', $file, 'custom-name.jpg');

        $this->assertSame('custom-name.jpg', $path);
        Storage::disk('public')->assertExists('custom-name.jpg');
    }

    public function test_upload_denied_extension_is_rejected(): void
    {
        $file = UploadedFile::fake()->create('script.php', 100);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tipo di file non consentito');

        $this->service->upload('public', '', $file);
    }

    public function test_upload_denied_extension_via_custom_name(): void
    {
        $file = UploadedFile::fake()->create('safe.txt', 100);

        $this->expectException(\InvalidArgumentException::class);

        $this->service->upload('public', '', $file, 'evil.php');
    }

    public function test_rename_file(): void
    {
        Storage::disk('public')->put('old-name.txt', 'content');

        $newPath = $this->service->rename('public', 'old-name.txt', 'new-name.txt');

        $this->assertSame('new-name.txt', $newPath);
        Storage::disk('public')->assertMissing('old-name.txt');
        Storage::disk('public')->assertExists('new-name.txt');
    }

    public function test_rename_file_in_subdirectory(): void
    {
        Storage::disk('public')->put('docs/report.txt', 'content');

        $newPath = $this->service->rename('public', 'docs/report.txt', 'summary.txt');

        $this->assertSame('docs/summary.txt', $newPath);
        Storage::disk('public')->assertExists('docs/summary.txt');
        Storage::disk('public')->assertMissing('docs/report.txt');
    }

    public function test_rename_to_existing_name_fails(): void
    {
        Storage::disk('public')->put('file-a.txt', 'a');
        Storage::disk('public')->put('file-b.txt', 'b');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('esiste già');

        $this->service->rename('public', 'file-a.txt', 'file-b.txt');
    }

    public function test_rename_to_denied_extension_fails(): void
    {
        Storage::disk('public')->put('safe.txt', 'content');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tipo di file non consentito');

        $this->service->rename('public', 'safe.txt', 'evil.php');
    }

    public function test_delete_file(): void
    {
        Storage::disk('public')->put('delete-me.txt', 'content');

        $this->service->delete('public', 'delete-me.txt');

        Storage::disk('public')->assertMissing('delete-me.txt');
    }

    public function test_delete_directory(): void
    {
        Storage::disk('public')->put('delete-dir/file.txt', 'content');

        $this->service->delete('public', 'delete-dir');

        Storage::disk('public')->assertMissing('delete-dir/file.txt');
    }

    public function test_delete_bulk(): void
    {
        Storage::disk('public')->put('a.txt', 'a');
        Storage::disk('public')->put('b.txt', 'b');
        Storage::disk('public')->put('c.txt', 'c');

        $count = $this->service->deleteBulk('public', ['a.txt', 'b.txt']);

        $this->assertSame(2, $count);
        Storage::disk('public')->assertMissing('a.txt');
        Storage::disk('public')->assertMissing('b.txt');
        Storage::disk('public')->assertExists('c.txt');
    }

    public function test_download_returns_streamed_response(): void
    {
        Storage::disk('public')->put('download-me.txt', 'file content');

        $response = $this->service->download('public', 'download-me.txt');

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
    }

    public function test_move_file(): void
    {
        Storage::disk('public')->put('file.txt', 'content');
        Storage::disk('public')->makeDirectory('target');

        $newPath = $this->service->move('public', 'file.txt', 'target');

        $this->assertSame('target/file.txt', $newPath);
        Storage::disk('public')->assertExists('target/file.txt');
        Storage::disk('public')->assertMissing('file.txt');
    }

    public function test_move_file_to_root(): void
    {
        Storage::disk('public')->put('nested/file.txt', 'content');

        $newPath = $this->service->move('public', 'nested/file.txt', '');

        $this->assertSame('file.txt', $newPath);
        Storage::disk('public')->assertExists('file.txt');
        Storage::disk('public')->assertMissing('nested/file.txt');
    }

    public function test_directory_traversal_on_upload_rejected(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg');

        $this->expectException(\InvalidArgumentException::class);

        $this->service->upload('public', '../etc', $file);
    }

    public function test_directory_traversal_on_rename_rejected(): void
    {
        Storage::disk('public')->put('file.txt', 'content');

        $this->expectException(\InvalidArgumentException::class);

        $this->service->rename('public', '../etc/passwd', 'new.txt');
    }

    public function test_directory_traversal_on_delete_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->delete('public', '../etc/passwd');
    }
}
