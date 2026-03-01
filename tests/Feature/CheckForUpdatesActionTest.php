<?php

namespace MmesDesign\FilamentFileManager\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use MmesDesign\FilamentFileManager\Filament\Pages\FileManagerPage;
use Tests\TestCase;

class CheckForUpdatesActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.env' => 'local']);
    }

    public function test_check_for_updates_action_exists(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPage::class)
            ->assertActionExists('checkForUpdates');
    }

    public function test_shows_up_to_date_notification_when_current_version(): void
    {
        $installedVersion = \Composer\InstalledVersions::getPrettyVersion('mmes-design/filament-file-manager');

        Http::fake([
            'repo.packagist.org/*' => Http::response([
                'packages' => [
                    'mmes-design/filament-file-manager' => [
                        ['version' => $installedVersion],
                    ],
                ],
            ]),
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPage::class)
            ->callAction('checkForUpdates')
            ->assertNotified(__('filament-file-manager::file-manager.actions.up_to_date_title'));
    }

    public function test_shows_update_available_notification_when_new_version(): void
    {
        Http::fake([
            'repo.packagist.org/*' => Http::response([
                'packages' => [
                    'mmes-design/filament-file-manager' => [
                        ['version' => '99.0.0'],
                    ],
                ],
            ]),
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPage::class)
            ->callAction('checkForUpdates')
            ->assertNotified(__('filament-file-manager::file-manager.actions.update_available_title'));
    }

    public function test_shows_error_notification_on_network_failure(): void
    {
        Http::fake([
            'repo.packagist.org/*' => fn () => throw new \RuntimeException('Connection failed'),
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManagerPage::class)
            ->callAction('checkForUpdates')
            ->assertNotified(__('filament-file-manager::file-manager.actions.update_check_failed_title'));
    }
}
