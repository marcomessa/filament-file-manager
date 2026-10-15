<?php

namespace MmesDesign\FilamentFileManager\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use MmesDesign\FilamentFileManager\Livewire\FileManagerPicker;
use Tests\TestCase;

class FileManagerPickerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.env' => 'local']);
        Storage::fake('public');
    }

    public function test_picker_renders_successfully(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class)
            ->assertSuccessful();
    }

    public function test_picker_mounts_with_default_disk(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class)
            ->assertSet('currentDisk', 'public');
    }

    public function test_picker_mounts_with_custom_disk(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, ['disk' => 'local'])
            ->assertSet('currentDisk', 'local');
    }

    public function test_picker_lists_files(): void
    {
        Storage::disk('public')->put('photo.jpg', 'image-content');
        Storage::disk('public')->put('document.pdf', 'pdf-content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class)
            ->assertSee('photo.jpg')
            ->assertSee('document.pdf');
    }

    public function test_picker_navigates_to_folder(): void
    {
        Storage::disk('public')->makeDirectory('images');
        Storage::disk('public')->put('images/photo.jpg', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class)
            ->call('navigateTo', 'images')
            ->assertSet('currentPath', 'images')
            ->assertSee('photo.jpg');
    }

    public function test_single_selection_replaces_previous(): void
    {
        Storage::disk('public')->put('file1.txt', 'content');
        Storage::disk('public')->put('file2.txt', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, ['multiple' => false])
            ->call('toggleSelection', 'file1.txt')
            ->assertSet('selectedItems', ['file1.txt'])
            ->call('toggleSelection', 'file2.txt')
            ->assertSet('selectedItems', ['file2.txt']);
    }

    public function test_single_selection_deselects_on_same_item(): void
    {
        Storage::disk('public')->put('file1.txt', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, ['multiple' => false])
            ->call('toggleSelection', 'file1.txt')
            ->assertSet('selectedItems', ['file1.txt'])
            ->call('toggleSelection', 'file1.txt')
            ->assertSet('selectedItems', []);
    }

    public function test_multiple_selection_adds_items(): void
    {
        Storage::disk('public')->put('file1.txt', 'content');
        Storage::disk('public')->put('file2.txt', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, ['multiple' => true])
            ->call('toggleSelection', 'file1.txt')
            ->call('toggleSelection', 'file2.txt')
            ->assertSet('selectedItems', ['file1.txt', 'file2.txt']);
    }

    public function test_multiple_selection_toggles_items(): void
    {
        Storage::disk('public')->put('file1.txt', 'content');
        Storage::disk('public')->put('file2.txt', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, ['multiple' => true])
            ->call('toggleSelection', 'file1.txt')
            ->call('toggleSelection', 'file2.txt')
            ->call('toggleSelection', 'file1.txt')
            ->assertSet('selectedItems', ['file2.txt']);
    }

    public function test_confirm_selection_dispatches_event_single(): void
    {
        Storage::disk('public')->put('file1.txt', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, ['multiple' => false, 'fieldId' => 'test-field'])
            ->call('toggleSelection', 'file1.txt')
            ->call('confirmSelection')
            ->assertDispatched('file-picker-selected', paths: 'file1.txt', fieldId: 'test-field');
    }

    public function test_confirm_selection_dispatches_event_multiple(): void
    {
        Storage::disk('public')->put('file1.txt', 'content');
        Storage::disk('public')->put('file2.txt', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, ['multiple' => true, 'fieldId' => 'test-field'])
            ->call('toggleSelection', 'file1.txt')
            ->call('toggleSelection', 'file2.txt')
            ->call('confirmSelection')
            ->assertDispatched('file-picker-selected', paths: ['file1.txt', 'file2.txt'], fieldId: 'test-field');
    }

    public function test_confirm_selection_dispatches_null_when_empty(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, ['multiple' => false, 'fieldId' => 'test-field'])
            ->call('confirmSelection')
            ->assertDispatched('file-picker-selected', paths: null, fieldId: 'test-field');
    }

    public function test_picker_filters_by_accepted_categories(): void
    {
        Storage::disk('public')->put('photo.jpg', 'image-content');
        Storage::disk('public')->put('document.pdf', 'pdf-content');
        Storage::disk('public')->put('song.mp3', 'audio-content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, ['acceptedCategories' => ['image']])
            ->assertSee('photo.jpg')
            ->assertDontSee('document.pdf')
            ->assertDontSee('song.mp3');
    }

    public function test_picker_shows_all_files_without_category_filter(): void
    {
        Storage::disk('public')->put('photo.jpg', 'image-content');
        Storage::disk('public')->put('document.pdf', 'pdf-content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class)
            ->assertSee('photo.jpg')
            ->assertSee('document.pdf');
    }

    public function test_picker_set_view_mode(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class)
            ->assertSet('viewMode', 'grid')
            ->call('setViewMode', 'list')
            ->assertSet('viewMode', 'list');
    }

    public function test_picker_sort_field(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class)
            ->assertSet('sortField', 'name')
            ->assertSet('sortDirection', 'asc')
            ->call('setSortField', 'name')
            ->assertSet('sortDirection', 'desc')
            ->call('setSortField', 'size')
            ->assertSet('sortField', 'size')
            ->assertSet('sortDirection', 'asc');
    }

    public function test_picker_mounts_with_selected_paths_string(): void
    {
        Storage::disk('public')->makeDirectory('albums/covers');
        Storage::disk('public')->put('albums/covers/photo.jpg', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, ['selectedPaths' => 'albums/covers/photo.jpg'])
            ->assertSet('selectedItems', ['albums/covers/photo.jpg'])
            ->assertSet('currentPath', 'albums/covers');
    }

    public function test_picker_mounts_with_selected_paths_array(): void
    {
        Storage::disk('public')->makeDirectory('albums/covers');
        Storage::disk('public')->put('albums/covers/photo.jpg', 'content');
        Storage::disk('public')->put('albums/covers/banner.jpg', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, [
            'selectedPaths' => ['albums/covers/photo.jpg', 'albums/covers/banner.jpg'],
        ])
            ->assertSet('selectedItems', ['albums/covers/photo.jpg', 'albums/covers/banner.jpg'])
            ->assertSet('currentPath', 'albums/covers');
    }

    public function test_picker_mounts_with_selected_paths_null(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, ['selectedPaths' => null])
            ->assertSet('selectedItems', [])
            ->assertSet('currentPath', '');
    }

    public function test_picker_mounts_with_root_level_selected_path(): void
    {
        Storage::disk('public')->put('photo.jpg', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, ['selectedPaths' => 'photo.jpg'])
            ->assertSet('selectedItems', ['photo.jpg'])
            ->assertSet('currentPath', '');
    }

    public function test_single_mode_status_bar_shows_singular_text(): void
    {
        Storage::disk('public')->put('file1.txt', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, ['multiple' => false])
            ->call('toggleSelection', 'file1.txt')
            ->assertSee(__('filament-file-manager::file-manager.labels.file_selected'));
    }

    public function test_multiple_mode_status_bar_shows_plural_text(): void
    {
        Storage::disk('public')->put('file1.txt', 'content');
        Storage::disk('public')->put('file2.txt', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, ['multiple' => true])
            ->call('toggleSelection', 'file1.txt')
            ->call('toggleSelection', 'file2.txt')
            ->assertSee(__('filament-file-manager::file-manager.labels.selected', ['count' => 2]));
    }

    public function test_select_all_is_noop_in_single_mode(): void
    {
        Storage::disk('public')->put('file1.txt', 'content');
        Storage::disk('public')->put('file2.txt', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, ['multiple' => false])
            ->call('selectAll')
            ->assertSet('selectedItems', []);
    }

    public function test_picker_category_filter_preserves_folders(): void
    {
        Storage::disk('public')->makeDirectory('images');
        Storage::disk('public')->put('document.pdf', 'pdf-content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, ['acceptedCategories' => ['image']])
            ->assertSee('images')
            ->assertDontSee('document.pdf');
    }

    public function test_picker_keeps_selection_across_folders(): void
    {
        Storage::disk('public')->put('products/cerec.png', 'content');
        Storage::disk('public')->put('products/latest/cutter.jpg', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, ['multiple' => true, 'fieldId' => 'test-field'])
            ->call('navigateTo', 'products')
            ->call('toggleSelection', 'products/cerec.png')
            ->call('navigateTo', 'products/latest')
            ->call('toggleSelection', 'products/latest/cutter.jpg')
            ->call('navigateUp')
            ->assertSet('selectedItems', ['products/cerec.png', 'products/latest/cutter.jpg'])
            ->call('confirmSelection')
            ->assertDispatched('file-picker-selected', paths: ['products/cerec.png', 'products/latest/cutter.jpg'], fieldId: 'test-field');
    }

    public function test_picker_keeps_preselected_paths_after_navigation(): void
    {
        Storage::disk('public')->put('products/cerec.png', 'content');
        Storage::disk('public')->put('products/latest/cutter.jpg', 'content');
        Storage::disk('public')->put('products/latest/new.jpg', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, [
            'multiple' => true,
            'fieldId' => 'test-field',
            'selectedPaths' => ['products/cerec.png', 'products/latest/cutter.jpg'],
        ])
            ->call('navigateTo', 'products/latest')
            ->call('toggleSelection', 'products/latest/new.jpg')
            ->call('confirmSelection')
            ->assertDispatched('file-picker-selected', paths: ['products/cerec.png', 'products/latest/cutter.jpg', 'products/latest/new.jpg'], fieldId: 'test-field');
    }

    public function test_picker_keeps_single_selection_across_folders(): void
    {
        Storage::disk('public')->put('products/cerec.png', 'content');
        Storage::disk('public')->makeDirectory('docs');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, ['multiple' => false])
            ->call('navigateTo', 'products')
            ->call('toggleSelection', 'products/cerec.png')
            ->call('navigateTo', 'docs')
            ->assertSet('selectedItems', ['products/cerec.png']);
    }

    public function test_selection_summary_lists_files_from_other_folders(): void
    {
        Storage::disk('public')->put('products/cerec.png', 'content');
        Storage::disk('public')->makeDirectory('docs');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, ['multiple' => true])
            ->call('navigateTo', 'products')
            ->call('toggleSelection', 'products/cerec.png')
            ->call('navigateTo', 'docs')
            ->assertSee(__('filament-file-manager::file-manager.labels.selected_files'))
            ->assertSee('products/')
            ->assertSee('cerec.png');
    }

    public function test_selection_summary_is_hidden_without_selection(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, ['multiple' => true])
            ->assertDontSee(__('filament-file-manager::file-manager.labels.selected_files'));
    }

    public function test_selection_summary_removes_item(): void
    {
        Storage::disk('public')->put('products/cerec.png', 'content');
        Storage::disk('public')->put('docs/manual.pdf', 'content');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class, [
            'multiple' => true,
            'selectedPaths' => ['products/cerec.png', 'docs/manual.pdf'],
        ])
            ->call('toggleSelection', 'docs/manual.pdf')
            ->assertSet('selectedItems', ['products/cerec.png'])
            ->assertDontSee('manual.pdf');
    }
}
