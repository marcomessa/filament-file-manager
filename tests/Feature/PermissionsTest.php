<?php

namespace MmesDesign\FilamentFileManager\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use MmesDesign\FilamentFileManager\FileManagerPlugin;
use MmesDesign\FilamentFileManager\Livewire\FileManager;
use MmesDesign\FilamentFileManager\Tests\Feature\Concerns\ResetsPermissions;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;
    use ResetsPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.env' => 'local']);
        Storage::fake('public');
        $this->resetPermissions();
    }

    // --- Backward compatibility ---

    public function test_all_permissions_default_to_true_when_no_closures_configured(): void
    {
        $plugin = FileManagerPlugin::make();

        $this->assertTrue($plugin->hasAccess());
        $this->assertTrue($plugin->canUserUpload());
        $this->assertTrue($plugin->canUserDelete());
        $this->assertTrue($plugin->canUserRename());
        $this->assertTrue($plugin->canUserMove());
        $this->assertTrue($plugin->canUserDownload());
        $this->assertTrue($plugin->canUserCreateFolder());
    }

    public function test_permissions_array_defaults_all_true(): void
    {
        $plugin = FileManagerPlugin::make();
        $permissions = $plugin->getPermissions();

        foreach ($permissions as $key => $value) {
            $this->assertTrue($value, "Permission '{$key}' should default to true");
        }
    }

    // --- Boolean values ---

    public function test_can_set_permission_to_false_with_boolean(): void
    {
        $plugin = FileManagerPlugin::make();
        $plugin->canUpload(false);

        $this->assertFalse($plugin->canUserUpload());
    }

    public function test_can_set_permission_to_true_with_boolean(): void
    {
        $plugin = FileManagerPlugin::make();
        $plugin->canUpload(true);

        $this->assertTrue($plugin->canUserUpload());
    }

    // --- Closure values ---

    public function test_closure_returning_false_denies_permission(): void
    {
        $plugin = FileManagerPlugin::make();
        $plugin->canDelete(fn () => false);

        $this->assertFalse($plugin->canUserDelete());
    }

    public function test_closure_returning_true_allows_permission(): void
    {
        $plugin = FileManagerPlugin::make();
        $plugin->canRename(fn () => true);

        $this->assertTrue($plugin->canUserRename());
    }

    // --- Fluent API returns static ---

    public function test_fluent_setters_return_plugin_instance(): void
    {
        $plugin = FileManagerPlugin::make();

        $this->assertSame($plugin, $plugin->canAccess(true));
        $this->assertSame($plugin, $plugin->canUpload(true));
        $this->assertSame($plugin, $plugin->canDelete(true));
        $this->assertSame($plugin, $plugin->canRename(true));
        $this->assertSame($plugin, $plugin->canMove(true));
        $this->assertSame($plugin, $plugin->canDownload(true));
        $this->assertSame($plugin, $plugin->canCreateFolder(true));
    }

    // --- Page access ---

    public function test_page_accessible_by_default(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/file-manager')
            ->assertOk();
    }

    public function test_page_returns_403_when_access_denied(): void
    {
        $plugin = FileManagerPlugin::get();
        $plugin->canAccess(fn () => false);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/file-manager')
            ->assertForbidden();
    }

    // --- Upload action visibility ---

    public function test_upload_action_visible_by_default(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManager::class)
            ->assertActionVisible('uploadFiles');
    }

    public function test_upload_action_hidden_when_denied(): void
    {
        $plugin = FileManagerPlugin::get();
        $plugin->canUpload(false);

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManager::class)
            ->assertActionHidden('uploadFiles');
    }

    // --- Create folder action visibility ---

    public function test_create_folder_action_visible_by_default(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManager::class)
            ->assertActionVisible('createFolder');
    }

    public function test_create_folder_action_hidden_when_denied(): void
    {
        $plugin = FileManagerPlugin::get();
        $plugin->canCreateFolder(false);

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManager::class)
            ->assertActionHidden('createFolder');
    }

    // --- Rename action visibility ---

    public function test_rename_action_hidden_when_denied(): void
    {
        $plugin = FileManagerPlugin::get();
        $plugin->canRename(false);

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManager::class)
            ->assertActionHidden('rename');
    }

    // --- Delete action visibility ---

    public function test_delete_action_hidden_when_denied(): void
    {
        $plugin = FileManagerPlugin::get();
        $plugin->canDelete(false);

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManager::class)
            ->assertActionHidden('deleteItem');
    }

    // --- Bulk actions visibility ---

    public function test_bulk_delete_action_hidden_when_delete_denied(): void
    {
        $plugin = FileManagerPlugin::get();
        $plugin->canDelete(false);

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManager::class)
            ->assertActionHidden('deleteSelected');
    }

    public function test_bulk_move_action_hidden_when_move_denied(): void
    {
        $plugin = FileManagerPlugin::get();
        $plugin->canMove(false);

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManager::class)
            ->assertActionHidden('moveSelected');
    }

    // --- Server-side enforcement ---

    public function test_move_item_aborts_when_denied(): void
    {
        Storage::disk('public')->put('file.txt', 'content');
        Storage::disk('public')->makeDirectory('target');

        $plugin = FileManagerPlugin::get();
        $plugin->canMove(false);

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManager::class)
            ->call('moveItem', 'file.txt', 'target')
            ->assertForbidden();

        Storage::disk('public')->assertExists('file.txt');
    }

    public function test_download_aborts_when_denied(): void
    {
        Storage::disk('public')->put('file.txt', 'content');

        $plugin = FileManagerPlugin::get();
        $plugin->canDownload(false);

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManager::class)
            ->call('downloadFile', 'file.txt')
            ->assertForbidden();
    }

    // --- Permissions passed to views ---

    public function test_permissions_array_passed_to_view(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $component = Livewire::test(FileManager::class);

        $this->assertArrayHasKey('permissions', $component->instance()->render()->getData());
    }

    // --- getPermissions structure ---

    public function test_get_permissions_returns_correct_keys(): void
    {
        $plugin = FileManagerPlugin::make();
        $permissions = $plugin->getPermissions();

        $this->assertArrayHasKey('canUpload', $permissions);
        $this->assertArrayHasKey('canDelete', $permissions);
        $this->assertArrayHasKey('canRename', $permissions);
        $this->assertArrayHasKey('canMove', $permissions);
        $this->assertArrayHasKey('canDownload', $permissions);
        $this->assertArrayHasKey('canCreateFolder', $permissions);
    }

    public function test_get_permissions_reflects_configured_values(): void
    {
        $plugin = FileManagerPlugin::make();
        $plugin->canUpload(false);
        $plugin->canDelete(fn () => false);
        $plugin->canRename(true);

        $permissions = $plugin->getPermissions();

        $this->assertFalse($permissions['canUpload']);
        $this->assertFalse($permissions['canDelete']);
        $this->assertTrue($permissions['canRename']);
        $this->assertTrue($permissions['canMove']);
        $this->assertTrue($permissions['canDownload']);
        $this->assertTrue($permissions['canCreateFolder']);
    }
}
