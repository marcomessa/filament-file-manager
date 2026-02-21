<?php

namespace MmesDesign\FilamentFileManager\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SelectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.env' => 'local']);
        Storage::fake('public');
    }

    public function test_toggle_selection_adds_item(): void
    {
        Storage::disk('public')->put('file.txt', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->call('toggleSelection', 'file.txt')
            ->assertSet('selectedItems', ['file.txt']);
    }

    public function test_toggle_selection_removes_item(): void
    {
        Storage::disk('public')->put('file.txt', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->call('toggleSelection', 'file.txt')
            ->assertSet('selectedItems', ['file.txt'])
            ->call('toggleSelection', 'file.txt')
            ->assertSet('selectedItems', []);
    }

    public function test_select_all_selects_all_items(): void
    {
        Storage::disk('public')->put('file1.txt', 'content');
        Storage::disk('public')->put('file2.txt', 'content');
        Storage::disk('public')->makeDirectory('folder1');

        $user = User::factory()->create();
        $this->actingAs($user);

        $component = Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->call('selectAll');

        $selected = $component->get('selectedItems');
        $this->assertCount(3, $selected);
        $this->assertContains('folder1', $selected);
        $this->assertContains('file1.txt', $selected);
        $this->assertContains('file2.txt', $selected);
    }

    public function test_clear_selection_empties_selection(): void
    {
        Storage::disk('public')->put('file.txt', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->call('toggleSelection', 'file.txt')
            ->assertSet('selectedItems', ['file.txt'])
            ->call('clearSelection')
            ->assertSet('selectedItems', []);
    }

    public function test_delete_selected_removes_files(): void
    {
        Storage::disk('public')->put('file1.txt', 'content1');
        Storage::disk('public')->put('file2.txt', 'content2');
        Storage::disk('public')->put('keep.txt', 'keep');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->set('selectedItems', ['file1.txt', 'file2.txt'])
            ->callAction('deleteSelected')
            ->assertHasNoActionErrors();

        Storage::disk('public')->assertMissing('file1.txt');
        Storage::disk('public')->assertMissing('file2.txt');
        Storage::disk('public')->assertExists('keep.txt');
    }

    public function test_delete_selected_clears_selection(): void
    {
        Storage::disk('public')->put('file1.txt', 'content1');
        Storage::disk('public')->put('file2.txt', 'content2');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->set('selectedItems', ['file1.txt', 'file2.txt'])
            ->callAction('deleteSelected')
            ->assertSet('selectedItems', []);
    }

    public function test_move_selected_moves_files(): void
    {
        Storage::disk('public')->put('file1.txt', 'content1');
        Storage::disk('public')->put('file2.txt', 'content2');
        Storage::disk('public')->makeDirectory('target');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->set('selectedItems', ['file1.txt', 'file2.txt'])
            ->callAction('moveSelected', data: ['destination' => 'target'])
            ->assertHasNoActionErrors();

        Storage::disk('public')->assertExists('target/file1.txt');
        Storage::disk('public')->assertExists('target/file2.txt');
        Storage::disk('public')->assertMissing('file1.txt');
        Storage::disk('public')->assertMissing('file2.txt');
    }

    public function test_move_selected_clears_selection(): void
    {
        Storage::disk('public')->put('file1.txt', 'content1');
        Storage::disk('public')->makeDirectory('target');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->set('selectedItems', ['file1.txt'])
            ->callAction('moveSelected', data: ['destination' => 'target'])
            ->assertSet('selectedItems', []);
    }

    public function test_move_item_moves_single_file(): void
    {
        Storage::disk('public')->put('document.txt', 'content');
        Storage::disk('public')->makeDirectory('folder');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->call('moveItem', 'document.txt', 'folder');

        Storage::disk('public')->assertExists('folder/document.txt');
        Storage::disk('public')->assertMissing('document.txt');
    }

    public function test_get_selected_count(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $component = Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->set('selectedItems', ['file1.txt', 'file2.txt']);

        $this->assertSame(2, $component->instance()->getSelectedCount());
    }

    public function test_is_selected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $component = Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->set('selectedItems', ['file1.txt']);

        $this->assertTrue($component->instance()->isSelected('file1.txt'));
        $this->assertFalse($component->instance()->isSelected('file2.txt'));
    }
}
