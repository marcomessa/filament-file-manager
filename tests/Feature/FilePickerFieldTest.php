<?php

namespace MmesDesign\FilamentFileManager\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use MmesDesign\FilamentFileManager\Enums\FileCategory;
use MmesDesign\FilamentFileManager\Forms\Components\FilePicker;
use Tests\TestCase;

class FilePickerFieldTest extends TestCase
{
    use RefreshDatabase;

    public function test_field_can_be_created(): void
    {
        $field = FilePicker::make('image');

        $this->assertInstanceOf(FilePicker::class, $field);
    }

    public function test_field_default_is_not_multiple(): void
    {
        $field = FilePicker::make('image');

        $this->assertFalse($field->isMultiple());
    }

    public function test_field_can_be_set_to_multiple(): void
    {
        $field = FilePicker::make('documents')->multiple();

        $this->assertTrue($field->isMultiple());
    }

    public function test_field_can_set_disk(): void
    {
        $field = FilePicker::make('image')->disk('s3');

        $this->assertSame('s3', $field->getDisk());
    }

    public function test_field_disk_defaults_to_null(): void
    {
        $field = FilePicker::make('image');

        $this->assertNull($field->getDisk());
    }

    public function test_field_can_set_accepted_categories(): void
    {
        $field = FilePicker::make('image')
            ->acceptedCategories([FileCategory::Image, FileCategory::Video]);

        $categories = $field->getAcceptedCategories();

        $this->assertCount(2, $categories);
        $this->assertContains(FileCategory::Image, $categories);
        $this->assertContains(FileCategory::Video, $categories);
    }

    public function test_field_accepted_categories_defaults_to_empty(): void
    {
        $field = FilePicker::make('image');

        $this->assertSame([], $field->getAcceptedCategories());
    }

    public function test_field_accepted_category_values(): void
    {
        $field = FilePicker::make('image')
            ->acceptedCategories([FileCategory::Image]);

        $this->assertSame(['image'], $field->getAcceptedCategoryValues());
    }

    public function test_field_has_picker_action(): void
    {
        $field = FilePicker::make('image');

        $action = $field->getPickerAction();

        $this->assertSame('pick', $action->getName());
    }

    public function test_image_preview_is_false_by_default(): void
    {
        $field = FilePicker::make('file');

        $this->assertFalse($field->isImagePreview());
    }

    public function test_image_preview_auto_detects_from_accepted_categories(): void
    {
        $field = FilePicker::make('cover')
            ->acceptedCategories([FileCategory::Image]);

        $this->assertTrue($field->isImagePreview());
    }

    public function test_image_preview_can_be_set_manually(): void
    {
        $field = FilePicker::make('file')->imagePreview();

        $this->assertTrue($field->isImagePreview());
    }

    public function test_image_preview_is_false_with_mixed_categories(): void
    {
        $field = FilePicker::make('file')
            ->acceptedCategories([FileCategory::Image, FileCategory::Document]);

        $this->assertFalse($field->isImagePreview());
    }
}
