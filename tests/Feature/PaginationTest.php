<?php

namespace MmesDesign\FilamentFileManager\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use MmesDesign\FilamentFileManager\Livewire\FileManager;
use MmesDesign\FilamentFileManager\Livewire\FileManagerPicker;
use MmesDesign\FilamentFileManager\Services\FileManagerService;
use Tests\TestCase;

class PaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.env' => 'local']);
        config(['filament-file-manager.per_page' => 5]);
        Storage::fake('public');
    }

    public function test_paginated_listing_returns_limited_files(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Storage::disk('public')->put("file{$i}.txt", "content {$i}");
        }

        $service = app(FileManagerService::class);

        $result = $service->listDirectoryPaginated('public', '', perPage: 5);

        $this->assertCount(5, $result['listing']->files);
        $this->assertSame(10, $result['totalFiles']);
        $this->assertTrue($result['hasMore']);
    }

    public function test_paginated_listing_page_two_returns_more_files(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Storage::disk('public')->put("file{$i}.txt", "content {$i}");
        }

        $service = app(FileManagerService::class);

        $result = $service->listDirectoryPaginated('public', '', page: 2, perPage: 5);

        $this->assertCount(10, $result['listing']->files);
        $this->assertSame(10, $result['totalFiles']);
        $this->assertFalse($result['hasMore']);
    }

    public function test_paginated_listing_has_no_more_when_all_fit(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            Storage::disk('public')->put("file{$i}.txt", "content {$i}");
        }

        $service = app(FileManagerService::class);

        $result = $service->listDirectoryPaginated('public', '', perPage: 5);

        $this->assertCount(3, $result['listing']->files);
        $this->assertSame(3, $result['totalFiles']);
        $this->assertFalse($result['hasMore']);
    }

    public function test_folders_always_fully_loaded(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Storage::disk('public')->makeDirectory("folder{$i}");
        }
        Storage::disk('public')->put('file.txt', 'content');

        $service = app(FileManagerService::class);

        $result = $service->listDirectoryPaginated('public', '', perPage: 5);

        $this->assertCount(10, $result['listing']->folders);
        $this->assertCount(1, $result['listing']->files);
    }

    public function test_load_more_increments_page(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Storage::disk('public')->put("file{$i}.txt", "content {$i}");
        }

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManager::class)
            ->assertSet('filePage', 1)
            ->assertViewHas('hasMoreFiles', true)
            ->call('loadMore')
            ->assertSet('filePage', 2)
            ->assertViewHas('hasMoreFiles', false);
    }

    public function test_load_more_does_nothing_when_no_more(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            Storage::disk('public')->put("file{$i}.txt", "content {$i}");
        }

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManager::class)
            ->assertSet('filePage', 1)
            ->assertViewHas('hasMoreFiles', false)
            ->call('loadMore')
            ->assertSet('filePage', 1);
    }

    public function test_navigation_resets_pagination(): void
    {
        Storage::disk('public')->makeDirectory('subfolder');
        for ($i = 1; $i <= 10; $i++) {
            Storage::disk('public')->put("file{$i}.txt", "content {$i}");
        }

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManager::class)
            ->call('loadMore')
            ->assertSet('filePage', 2)
            ->call('navigateTo', 'subfolder')
            ->assertSet('filePage', 1);
    }

    public function test_sort_change_resets_pagination(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Storage::disk('public')->put("file{$i}.txt", "content {$i}");
        }

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManager::class)
            ->call('loadMore')
            ->assertSet('filePage', 2)
            ->call('setSortField', 'size')
            ->assertSet('filePage', 1);
    }

    public function test_select_all_selects_all_files_not_just_loaded(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Storage::disk('public')->put("file{$i}.txt", "content {$i}");
        }

        $user = User::factory()->create();
        $this->actingAs($user);

        $component = Livewire::test(FileManager::class)
            ->assertViewHas('hasMoreFiles', true)
            ->call('selectAll');

        $selected = $component->get('selectedItems');
        $this->assertCount(10, $selected);
    }

    public function test_picker_pagination(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Storage::disk('public')->put("file{$i}.txt", "content {$i}");
        }

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class)
            ->assertSet('filePage', 1)
            ->assertViewHas('hasMoreFiles', true)
            ->call('loadMore')
            ->assertSet('filePage', 2)
            ->assertViewHas('hasMoreFiles', false);
    }

    public function test_picker_sort_resets_pagination(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Storage::disk('public')->put("file{$i}.txt", "content {$i}");
        }

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPicker::class)
            ->call('loadMore')
            ->assertSet('filePage', 2)
            ->call('setSortField', 'date')
            ->assertSet('filePage', 1);
    }
}
