<?php

namespace MmesDesign\FilamentFileManager\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use MmesDesign\FilamentFileManager\Tests\Feature\Concerns\ResetsPermissions;
use Tests\TestCase;

class RenameExtensionProtectionTest extends TestCase
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

    public function test_rename_action_prefills_filename(): void
    {
        Storage::disk('public')->put('photo.jpg', 'image content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->mountAction('rename', arguments: ['path' => 'photo.jpg'])
            ->assertSchemaStateSet([
                'newName' => 'photo.jpg',
            ]);
    }

    public function test_rename_action_prefills_filename_in_subdirectory(): void
    {
        Storage::disk('public')->put('docs/report.pdf', 'pdf content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->mountAction('rename', arguments: ['path' => 'docs/report.pdf'])
            ->assertSchemaStateSet([
                'newName' => 'report.pdf',
            ]);
    }

    public function test_rename_action_prefills_folder_name(): void
    {
        Storage::disk('public')->makeDirectory('my-folder');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->mountAction('rename', arguments: ['path' => 'my-folder'])
            ->assertSchemaStateSet([
                'newName' => 'my-folder',
                'originalExtension' => '',
            ]);
    }

    public function test_extension_changed_translation_key_exists_in_en(): void
    {
        $this->assertNotSame(
            'filament-file-manager::file-manager.messages.extension_changed',
            __('filament-file-manager::file-manager.messages.extension_changed'),
        );
    }

    public function test_extension_changed_translation_key_exists_in_it(): void
    {
        $this->assertNotSame(
            'filament-file-manager::file-manager.messages.extension_changed',
            __('filament-file-manager::file-manager.messages.extension_changed', [], 'it'),
        );
    }
}
