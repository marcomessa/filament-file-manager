<?php

namespace MmesDesign\FilamentFileManager\Tests\Feature;

use MmesDesign\FilamentFileManager\Enums\FileCategory;
use MmesDesign\FilamentFileManager\Forms\Components\FileManagerMarkdownEditor;
use Tests\TestCase;

class FileManagerMarkdownEditorTest extends TestCase
{
    public function test_field_can_be_created(): void
    {
        $field = FileManagerMarkdownEditor::make('content');

        $this->assertInstanceOf(FileManagerMarkdownEditor::class, $field);
    }

    public function test_field_defaults_to_multiple(): void
    {
        $field = FileManagerMarkdownEditor::make('content');

        $this->assertTrue($field->isFmMultiple());
    }

    public function test_field_can_disable_multiple(): void
    {
        $field = FileManagerMarkdownEditor::make('content')->fmMultiple(false);

        $this->assertFalse($field->isFmMultiple());
    }

    public function test_field_disk_defaults_to_null(): void
    {
        $field = FileManagerMarkdownEditor::make('content');

        $this->assertNull($field->getFmDisk());
    }

    public function test_field_can_set_disk(): void
    {
        $field = FileManagerMarkdownEditor::make('content')->fmDisk('s3');

        $this->assertSame('s3', $field->getFmDisk());
    }

    public function test_field_accepted_categories_defaults_to_empty(): void
    {
        $field = FileManagerMarkdownEditor::make('content');

        $this->assertSame([], $field->getFmAcceptedCategories());
    }

    public function test_field_can_set_accepted_categories(): void
    {
        $field = FileManagerMarkdownEditor::make('content')
            ->fmAcceptedCategories([FileCategory::Image, FileCategory::Video]);

        $categories = $field->getFmAcceptedCategories();

        $this->assertCount(2, $categories);
        $this->assertContains(FileCategory::Image, $categories);
        $this->assertContains(FileCategory::Video, $categories);
    }

    public function test_field_returns_accepted_category_values(): void
    {
        $field = FileManagerMarkdownEditor::make('content')
            ->fmAcceptedCategories([FileCategory::Image]);

        $this->assertSame(['image'], $field->getFmAcceptedCategoryValues());
    }

    public function test_field_has_file_manager_action(): void
    {
        $field = FileManagerMarkdownEditor::make('content');

        $action = $field->getFileManagerAction();

        $this->assertSame('fileManager', $action->getName());
    }

    public function test_field_fluent_api_returns_self(): void
    {
        $field = FileManagerMarkdownEditor::make('content');

        $this->assertSame($field, $field->fmDisk('public'));
        $this->assertSame($field, $field->fmMultiple(false));
        $this->assertSame($field, $field->fmAcceptedCategories([FileCategory::Image]));
    }
}
