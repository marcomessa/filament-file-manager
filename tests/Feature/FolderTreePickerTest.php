<?php

namespace MmesDesign\FilamentFileManager\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use MmesDesign\FilamentFileManager\Forms\Components\FolderTreePicker;
use MmesDesign\FilamentFileManager\Tests\Feature\Concerns\ResetsPermissions;
use Tests\TestCase;

class FolderTreePickerTest extends TestCase
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

    public function test_folder_tree_picker_returns_root_subfolders(): void
    {
        Storage::disk('public')->makeDirectory('documents');
        Storage::disk('public')->makeDirectory('images');
        Storage::disk('public')->makeDirectory('videos');

        $picker = FolderTreePicker::make('destination')->disk('public');

        $result = $picker->getSubfolders('');

        $names = array_column($result, 'name');
        $this->assertContains('documents', $names);
        $this->assertContains('images', $names);
        $this->assertContains('videos', $names);
        $this->assertCount(3, $result);
    }

    public function test_folder_tree_picker_returns_nested_subfolders(): void
    {
        Storage::disk('public')->makeDirectory('images/vacation');
        Storage::disk('public')->makeDirectory('images/work');
        Storage::disk('public')->makeDirectory('images/vacation/beach');

        $picker = FolderTreePicker::make('destination')->disk('public');

        $result = $picker->getSubfolders('images');

        $names = array_column($result, 'name');
        $this->assertContains('vacation', $names);
        $this->assertContains('work', $names);
        // Should only return direct children, not nested
        $this->assertNotContains('beach', $names);
        $this->assertCount(2, $result);
    }

    public function test_folder_tree_picker_returns_empty_for_empty_folder(): void
    {
        Storage::disk('public')->makeDirectory('empty-folder');

        $picker = FolderTreePicker::make('destination')->disk('public');

        $result = $picker->getSubfolders('empty-folder');

        $this->assertSame([], $result);
    }

    public function test_folder_tree_picker_returns_sorted_results(): void
    {
        Storage::disk('public')->makeDirectory('zebra');
        Storage::disk('public')->makeDirectory('alpha');
        Storage::disk('public')->makeDirectory('middle');

        $picker = FolderTreePicker::make('destination')->disk('public');

        $result = $picker->getSubfolders('');

        $names = array_column($result, 'name');
        $this->assertSame(['alpha', 'middle', 'zebra'], $names);
    }

    public function test_move_selected_with_folder_tree_picker_destination(): void
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

    public function test_move_selected_to_root(): void
    {
        Storage::disk('public')->put('subfolder/file1.txt', 'content1');
        Storage::disk('public')->put('subfolder/file2.txt', 'content2');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\MmesDesign\FilamentFileManager\Livewire\FileManager::class)
            ->call('navigateTo', 'subfolder')
            ->set('selectedItems', ['subfolder/file1.txt', 'subfolder/file2.txt'])
            ->callAction('moveSelected', data: ['destination' => ''])
            ->assertHasNoActionErrors();

        Storage::disk('public')->assertExists('file1.txt');
        Storage::disk('public')->assertExists('file2.txt');
        Storage::disk('public')->assertMissing('subfolder/file1.txt');
        Storage::disk('public')->assertMissing('subfolder/file2.txt');
    }
}
