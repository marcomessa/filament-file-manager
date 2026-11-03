<?php

namespace MmesDesign\FilamentFileManager\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use MmesDesign\FilamentFileManager\DTOs\FileItem;
use MmesDesign\FilamentFileManager\Enums\FileCategory;
use MmesDesign\FilamentFileManager\FileManagerPlugin;
use MmesDesign\FilamentFileManager\Livewire\FileManager;
use MmesDesign\FilamentFileManager\Support\ClipboardScript;
use MmesDesign\FilamentFileManager\Tests\Feature\Concerns\ResetsPermissions;
use Tests\TestCase;

class CopyUrlTest extends TestCase
{
    use RefreshDatabase;
    use ResetsPermissions;

    protected const CLIPBOARD_CALL = 'navigator.clipboard.writeText';

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.env' => 'local']);
        Storage::fake('public');
        $this->resetPermissions();
    }

    protected function makeFileItem(?string $url): FileItem
    {
        return new FileItem(
            name: 'photo.jpg',
            path: 'images/photo.jpg',
            size: 1024,
            lastModified: 1700000000,
            extension: 'jpg',
            category: FileCategory::Image,
            mimeType: 'image/jpeg',
            url: $url,
        );
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function actionComponents(): array
    {
        return [
            'list row actions' => ['<x-filament-file-manager::file-actions :item="$item" :permissions="$permissions" />'],
            'grid card menu' => ['<x-filament-file-manager::card-actions-menu :item="$item" :permissions="$permissions" />'],
        ];
    }

    #[DataProvider('actionComponents')]
    public function test_copy_url_action_is_rendered_for_files_with_public_url(string $component): void
    {
        $this->blade($component, [
            'item' => $this->makeFileItem('https://example.com/storage/images/photo.jpg'),
            'permissions' => ['canCopyUrl' => true],
        ])
            ->assertSee(self::CLIPBOARD_CALL, false)
            ->assertSee('example.com\/storage\/images\/photo.jpg', false)
            ->assertSee(__('filament-file-manager::file-manager.actions.copy_url'));
    }

    #[DataProvider('actionComponents')]
    public function test_copy_url_action_is_hidden_without_permission(string $component): void
    {
        $this->blade($component, [
            'item' => $this->makeFileItem('https://example.com/storage/images/photo.jpg'),
            'permissions' => ['canCopyUrl' => false],
        ])
            ->assertDontSee(self::CLIPBOARD_CALL, false);
    }

    #[DataProvider('actionComponents')]
    public function test_copy_url_action_is_hidden_for_files_without_public_url(string $component): void
    {
        $this->blade($component, [
            'item' => $this->makeFileItem(null),
            'permissions' => ['canCopyUrl' => true],
        ])
            ->assertDontSee(self::CLIPBOARD_CALL, false);
    }

    public function test_preview_sidebar_shows_copy_url_action_when_permitted(): void
    {
        Storage::disk('public')->put('photo.jpg', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManager::class)
            ->assertSee(self::CLIPBOARD_CALL, false);
    }

    public function test_copy_url_action_is_hidden_everywhere_without_permission(): void
    {
        Storage::disk('public')->put('photo.jpg', 'content');

        FileManagerPlugin::get()->canCopyUrl(false);

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManager::class)
            ->assertDontSee(self::CLIPBOARD_CALL, false);
    }

    public function test_get_permissions_includes_copy_url(): void
    {
        $plugin = FileManagerPlugin::get();

        $this->assertTrue($plugin->getPermissions('public', 'photo.jpg')['canCopyUrl']);

        $plugin->canCopyUrl(fn (string $disk, string $path): bool => $path !== 'secret.pdf');

        $this->assertTrue($plugin->getPermissions('public', 'photo.jpg')['canCopyUrl']);
        $this->assertFalse($plugin->getPermissions('public', 'secret.pdf')['canCopyUrl']);
    }

    public function test_clipboard_script_resolves_url_and_reports_outcome(): void
    {
        $script = ClipboardScript::copyUrl('previewFile.url');

        $this->assertStringContainsString('navigator.clipboard.writeText(new URL(previewFile.url, window.location.origin).href)', $script);
        $this->assertStringContainsString('.then(', $script);
        $this->assertStringContainsString('.catch(', $script);
        $this->assertStringContainsString('URL copied to clipboard', $script);
        $this->assertStringContainsString('Could not copy the URL', $script);
    }
}
