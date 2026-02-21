<?php

namespace MmesDesign\FilamentFileManager\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FileManagerPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.env' => 'local']);
        Storage::fake('public');
    }

    public function test_file_manager_page_requires_authentication(): void
    {
        $this->get('/admin/file-manager')
            ->assertRedirect();
    }

    public function test_livewire_component_shows_empty_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->assertSee('Questa cartella è vuota');
    }

    public function test_livewire_component_shows_files(): void
    {
        Storage::disk('public')->put('test-file.txt', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->assertSee('test-file.txt');
    }

    public function test_livewire_component_shows_folders(): void
    {
        Storage::disk('public')->makeDirectory('my-folder');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->assertSee('my-folder');
    }

    public function test_navigate_into_folder(): void
    {
        Storage::disk('public')->put('images/photo.jpg', 'image');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->call('navigateTo', 'images')
            ->assertSee('photo.jpg')
            ->assertSet('currentPath', 'images');
    }

    public function test_navigate_up(): void
    {
        Storage::disk('public')->put('images/photo.jpg', 'image');
        Storage::disk('public')->put('root.txt', 'root');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->call('navigateTo', 'images')
            ->assertSet('currentPath', 'images')
            ->call('navigateUp')
            ->assertSet('currentPath', '')
            ->assertSee('root.txt');
    }

    public function test_toggle_view_mode(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->assertSet('viewMode', 'grid')
            ->call('setViewMode', 'list')
            ->assertSet('viewMode', 'list');
    }

    public function test_switch_sort_field(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->assertSet('sortField', 'name')
            ->call('setSortField', 'size')
            ->assertSet('sortField', 'size')
            ->assertSet('sortDirection', 'asc');
    }

    public function test_toggle_sort_direction(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->assertSet('sortDirection', 'asc')
            ->call('setSortField', 'name')
            ->assertSet('sortDirection', 'desc');
    }

    public function test_breadcrumbs_at_root(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $component = Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class);
        $breadcrumbs = $component->instance()->getBreadcrumbs();

        $this->assertCount(1, $breadcrumbs);
        $this->assertSame('', $breadcrumbs[0]['path']);
    }

    public function test_breadcrumbs_in_nested_folder(): void
    {
        Storage::disk('public')->makeDirectory('images/vacation');

        $user = User::factory()->create();
        $this->actingAs($user);

        $component = Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->call('navigateTo', 'images/vacation');

        $breadcrumbs = $component->instance()->getBreadcrumbs();

        $this->assertCount(3, $breadcrumbs);
        $this->assertSame('', $breadcrumbs[0]['path']);
        $this->assertSame('images', $breadcrumbs[1]['path']);
        $this->assertSame('images/vacation', $breadcrumbs[2]['path']);
    }

    public function test_preview_action_can_be_mounted(): void
    {
        Storage::disk('public')->put('document.pdf', 'pdf content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->callAction('preview', arguments: ['path' => 'document.pdf'])
            ->assertOk();
    }

    public function test_preview_action_shows_file_name_in_heading(): void
    {
        Storage::disk('public')->put('report.pdf', 'pdf content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->mountAction('preview', arguments: ['path' => 'report.pdf'])
            ->assertSee('report.pdf');
    }

    public function test_generate_thumbnail_creates_thumbnail_for_valid_image(): void
    {
        $image = $this->createTestImage(400, 300);
        Storage::disk('public')->put('photo.jpg', $image);

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->call('generateThumbnail', 'photo.jpg');

        Storage::disk('public')->assertExists('.thumbnails/photo.jpg');
    }

    public function test_generate_thumbnail_does_not_create_for_non_image(): void
    {
        Storage::disk('public')->put('document.pdf', 'pdf content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->call('generateThumbnail', 'document.pdf');

        Storage::disk('public')->assertMissing('.thumbnails/document.pdf');
    }

    public function test_status_bar_shows_counts(): void
    {
        Storage::disk('public')->put('file1.txt', 'content');
        Storage::disk('public')->put('file2.txt', 'content');
        Storage::disk('public')->makeDirectory('folder1');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->assertSee('2 file')
            ->assertSee('1 cartella');
    }

    public function test_bulk_actions_are_registered(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->assertActionExists('deleteSelected')
            ->assertActionExists('moveSelected');
    }

    public function test_bulk_toolbar_visible_when_items_selected(): void
    {
        Storage::disk('public')->put('file.txt', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->set('selectedItems', ['file.txt'])
            ->assertSee('1 elemento selezionato')
            ->assertSee('Deseleziona');
    }

    public function test_sidebar_empty_state_and_structure_renders(): void
    {
        Storage::disk('public')->put('file.txt', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->assertSee('Seleziona un file per visualizzare i dettagli')
            ->assertSee('Informazioni');
    }

    public function test_file_preview_data_is_embedded_in_html(): void
    {
        Storage::disk('public')->put('report.pdf', 'pdf content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->assertSeeHtml('report.pdf');
    }

    public function test_file_item_to_preview_array(): void
    {
        $item = new \MmesDesign\FilamentFileManager\DTOs\FileItem(
            name: 'photo.jpg',
            path: 'images/photo.jpg',
            size: 2048,
            lastModified: 1700000000,
            extension: 'jpg',
            category: \MmesDesign\FilamentFileManager\Enums\FileCategory::Image,
            mimeType: 'image/jpeg',
            url: 'http://example.com/photo.jpg',
            thumbnailUrl: 'http://example.com/thumb.jpg',
        );

        $preview = $item->toPreviewArray();

        $this->assertSame('photo.jpg', $preview['name']);
        $this->assertSame('images/photo.jpg', $preview['path']);
        $this->assertSame('2 KB', $preview['formattedSize']);
        $this->assertSame('jpg', $preview['extension']);
        $this->assertSame('image/jpeg', $preview['mimeType']);
        $this->assertSame('image', $preview['category']);
        $this->assertSame('Image', $preview['categoryLabel']);
        $this->assertSame('http://example.com/photo.jpg', $preview['url']);
        $this->assertSame('http://example.com/thumb.jpg', $preview['thumbnailUrl']);
        $this->assertTrue($preview['isImage']);
    }

    public function test_bulk_toolbar_hidden_when_no_selection(): void
    {
        Storage::disk('public')->put('file.txt', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->assertDontSee('elemento selezionato')
            ->assertDontSee('elementi selezionati');
    }

    public function test_selection_count_in_status_bar(): void
    {
        Storage::disk('public')->put('file1.txt', 'content');
        Storage::disk('public')->put('file2.txt', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->set('selectedItems', ['file1.txt', 'file2.txt'])
            ->assertSee('2 selezionati');
    }

    /**
     * Create a real test image and return its binary content.
     */
    private function createTestImage(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($image, 100, 150, 200);
        imagefill($image, 0, 0, $color);

        ob_start();
        imagejpeg($image, null, 90);
        $content = ob_get_clean();
        imagedestroy($image);

        return $content;
    }
}
