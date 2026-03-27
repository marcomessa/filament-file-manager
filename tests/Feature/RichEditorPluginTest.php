<?php

namespace MmesDesign\FilamentFileManager\Tests\Feature;

use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use MmesDesign\FilamentFileManager\Enums\FileCategory;
use MmesDesign\FilamentFileManager\RichEditor\FileManagerRichEditorPlugin;
use Tests\TestCase;

class RichEditorPluginTest extends TestCase
{
    public function test_plugin_can_be_created(): void
    {
        $plugin = FileManagerRichEditorPlugin::make();

        $this->assertInstanceOf(FileManagerRichEditorPlugin::class, $plugin);
    }

    public function test_plugin_implements_rich_content_plugin(): void
    {
        $plugin = FileManagerRichEditorPlugin::make();

        $this->assertInstanceOf(RichContentPlugin::class, $plugin);
    }

    public function test_plugin_defaults_to_multiple(): void
    {
        $plugin = FileManagerRichEditorPlugin::make();

        $this->assertTrue($plugin->isMultiple());
    }

    public function test_plugin_can_disable_multiple(): void
    {
        $plugin = FileManagerRichEditorPlugin::make()->multiple(false);

        $this->assertFalse($plugin->isMultiple());
    }

    public function test_plugin_disk_defaults_to_null(): void
    {
        $plugin = FileManagerRichEditorPlugin::make();

        $this->assertNull($plugin->getDisk());
    }

    public function test_plugin_can_set_disk(): void
    {
        $plugin = FileManagerRichEditorPlugin::make()->disk('s3');

        $this->assertSame('s3', $plugin->getDisk());
    }

    public function test_plugin_accepted_categories_defaults_to_empty(): void
    {
        $plugin = FileManagerRichEditorPlugin::make();

        $this->assertSame([], $plugin->getAcceptedCategories());
    }

    public function test_plugin_can_set_accepted_categories(): void
    {
        $plugin = FileManagerRichEditorPlugin::make()
            ->acceptedCategories([FileCategory::Image, FileCategory::Document]);

        $categories = $plugin->getAcceptedCategories();

        $this->assertCount(2, $categories);
        $this->assertContains(FileCategory::Image, $categories);
        $this->assertContains(FileCategory::Document, $categories);
    }

    public function test_plugin_returns_accepted_category_values(): void
    {
        $plugin = FileManagerRichEditorPlugin::make()
            ->acceptedCategories([FileCategory::Image]);

        $this->assertSame(['image'], $plugin->getAcceptedCategoryValues());
    }

    public function test_plugin_returns_no_tiptap_php_extensions(): void
    {
        $plugin = FileManagerRichEditorPlugin::make();

        $this->assertSame([], $plugin->getTipTapPhpExtensions());
    }

    public function test_plugin_returns_no_tiptap_js_extensions(): void
    {
        $plugin = FileManagerRichEditorPlugin::make();

        $this->assertSame([], $plugin->getTipTapJsExtensions());
    }

    public function test_plugin_returns_one_editor_tool(): void
    {
        $plugin = FileManagerRichEditorPlugin::make();

        $tools = $plugin->getEditorTools();

        $this->assertCount(1, $tools);
        $this->assertSame('fileManager', $tools[0]->getName());
    }

    public function test_plugin_returns_one_editor_action(): void
    {
        $plugin = FileManagerRichEditorPlugin::make();

        $actions = $plugin->getEditorActions();

        $this->assertCount(1, $actions);
        $this->assertSame('fileManager', $actions[0]->getName());
    }

    public function test_plugin_fluent_api_returns_self(): void
    {
        $plugin = FileManagerRichEditorPlugin::make();

        $this->assertSame($plugin, $plugin->disk('public'));
        $this->assertSame($plugin, $plugin->multiple(false));
        $this->assertSame($plugin, $plugin->acceptedCategories([FileCategory::Image]));
    }
}
