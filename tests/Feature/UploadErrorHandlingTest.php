<?php

namespace MmesDesign\FilamentFileManager\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use MmesDesign\FilamentFileManager\Livewire\FileManager;
use Tests\TestCase;

class UploadErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_upload_error_produces_validation_error_on_field(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManager::class)
            ->call('_uploadErrored', 'mountedActions.0.data.files.fake-uuid', null, true)
            ->assertHasErrors('mountedActions.0.data.files.fake-uuid');
    }

    public function test_upload_error_message_does_not_contain_field_path(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $instance = Livewire::test(FileManager::class)->instance();

        try {
            $instance->_uploadErrored('mountedActions.0.data.files.fake-uuid', null, true);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $message = $e->errors()['mountedActions.0.data.files.fake-uuid'][0];

            $this->assertStringNotContainsString('mountedActions', $message);
            $this->assertStringNotContainsString('fake-uuid', $message);
        }
    }

    public function test_upload_error_message_uses_translated_text(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $instance = Livewire::test(FileManager::class)->instance();

        try {
            $instance->_uploadErrored('files', null, false);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $message = $e->errors()['files'][0];

            $this->assertMatchesRegularExpression('/\d+\s[KMGT]?B/', $message);
        }
    }

    public function test_upload_error_message_reflects_plugin_config_when_lowest(): void
    {
        config(['filament-file-manager.max_upload_size' => 1024]); // 1 MB — likely lower than PHP ini

        $user = User::factory()->create();
        $this->actingAs($user);

        $instance = Livewire::test(FileManager::class)->instance();

        try {
            $instance->_uploadErrored('files', null, false);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $message = $e->errors()['files'][0];

            $this->assertStringContainsString('1 MB', $message);
        }
    }

    public function test_effective_upload_limit_picks_minimum(): void
    {
        config(['filament-file-manager.max_upload_size' => 512]); // 512 KB — very low

        $user = User::factory()->create();
        $this->actingAs($user);

        $instance = Livewire::test(FileManager::class)->instance();
        $method = new \ReflectionMethod($instance, 'getEffectiveUploadLimit');

        $limit = $method->invoke($instance);

        $this->assertSame(512, $limit);
    }

    public function test_effective_upload_limit_respects_php_ini(): void
    {
        // Set plugin config very high so PHP ini becomes the bottleneck
        config(['filament-file-manager.max_upload_size' => 999999]);

        $user = User::factory()->create();
        $this->actingAs($user);

        $instance = Livewire::test(FileManager::class)->instance();
        $method = new \ReflectionMethod($instance, 'getEffectiveUploadLimit');

        $limit = $method->invoke($instance);

        // Should be limited by PHP's upload_max_filesize or post_max_size, not our config
        $this->assertLessThan(999999, $limit);
    }

    public function test_php_ini_to_kb_converts_megabytes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $instance = Livewire::test(FileManager::class)->instance();
        $method = new \ReflectionMethod($instance, 'phpIniToKb');

        $this->assertSame(2048, $method->invoke($instance, '2M'));
        $this->assertSame(131072, $method->invoke($instance, '128M'));
    }

    public function test_php_ini_to_kb_converts_gigabytes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $instance = Livewire::test(FileManager::class)->instance();
        $method = new \ReflectionMethod($instance, 'phpIniToKb');

        $this->assertSame(1048576, $method->invoke($instance, '1G'));
        $this->assertSame(2097152, $method->invoke($instance, '2G'));
    }

    public function test_php_ini_to_kb_converts_kilobytes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $instance = Livewire::test(FileManager::class)->instance();
        $method = new \ReflectionMethod($instance, 'phpIniToKb');

        $this->assertSame(512, $method->invoke($instance, '512K'));
    }

    public function test_php_ini_to_kb_converts_plain_bytes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $instance = Livewire::test(FileManager::class)->instance();
        $method = new \ReflectionMethod($instance, 'phpIniToKb');

        $this->assertSame(1, $method->invoke($instance, '1024'));
    }

    public function test_file_too_large_translation_exists_in_english(): void
    {
        app()->setLocale('en');

        $translated = __('filament-file-manager::file-manager.messages.file_too_large', ['max' => '50 MB']);

        $this->assertNotSame('filament-file-manager::file-manager.messages.file_too_large', $translated);
        $this->assertStringContainsString('50 MB', $translated);
    }

    public function test_upload_failed_translation_exists_in_english(): void
    {
        app()->setLocale('en');

        $translated = __('filament-file-manager::file-manager.messages.upload_failed', ['max' => '50 MB']);

        $this->assertNotSame('filament-file-manager::file-manager.messages.upload_failed', $translated);
        $this->assertStringContainsString('50 MB', $translated);
    }

    public function test_file_too_large_translation_exists_in_italian(): void
    {
        app()->setLocale('it');

        $translated = __('filament-file-manager::file-manager.messages.file_too_large', ['max' => '50 MB']);

        $this->assertNotSame('filament-file-manager::file-manager.messages.file_too_large', $translated);
        $this->assertStringContainsString('50 MB', $translated);
    }

    public function test_upload_failed_translation_exists_in_italian(): void
    {
        app()->setLocale('it');

        $translated = __('filament-file-manager::file-manager.messages.upload_failed', ['max' => '50 MB']);

        $this->assertNotSame('filament-file-manager::file-manager.messages.upload_failed', $translated);
        $this->assertStringContainsString('50 MB', $translated);
    }

    public function test_upload_action_exists(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FileManager::class)
            ->assertActionExists('uploadFiles');
    }
}
