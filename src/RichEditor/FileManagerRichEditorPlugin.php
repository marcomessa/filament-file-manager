<?php

namespace MmesDesign\FilamentFileManager\RichEditor;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Support\Icons\Heroicon;
use MmesDesign\FilamentFileManager\Enums\FileCategory;
use MmesDesign\FilamentFileManager\RichEditor\Actions\InsertFileAction;
use Tiptap\Core\Extension;

class FileManagerRichEditorPlugin implements RichContentPlugin
{
    protected string|Closure|null $disk = null;

    protected bool|Closure $isMultiple = true;

    /** @var array<int, FileCategory>|Closure */
    protected array|Closure $acceptedCategories = [];

    public static function make(): static
    {
        return app(static::class);
    }

    public function disk(string|Closure|null $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function getDisk(): ?string
    {
        return $this->disk instanceof Closure
            ? ($this->disk)()
            : $this->disk;
    }

    /**
     * @param  array<int, FileCategory>|Closure  $categories
     */
    public function acceptedCategories(array|Closure $categories): static
    {
        $this->acceptedCategories = $categories;

        return $this;
    }

    /**
     * @return array<int, FileCategory>
     */
    public function getAcceptedCategories(): array
    {
        return $this->acceptedCategories instanceof Closure
            ? ($this->acceptedCategories)()
            : $this->acceptedCategories;
    }

    /**
     * @return array<int, string>
     */
    public function getAcceptedCategoryValues(): array
    {
        return array_map(
            fn (FileCategory $category): string => $category->value,
            $this->getAcceptedCategories(),
        );
    }

    public function multiple(bool|Closure $condition = true): static
    {
        $this->isMultiple = $condition;

        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->isMultiple instanceof Closure
            ? ($this->isMultiple)()
            : $this->isMultiple;
    }

    /**
     * @return array<Extension>
     */
    public function getTipTapPhpExtensions(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    public function getTipTapJsExtensions(): array
    {
        return [];
    }

    /**
     * @return array<RichEditorTool>
     */
    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make('fileManager')
                ->label(__('filament-file-manager::file-manager.editors.file_manager'))
                ->icon(Heroicon::OutlinedFolderOpen)
                ->activeStyling(false)
                ->action(),
        ];
    }

    /**
     * @return array<Action>
     */
    public function getEditorActions(): array
    {
        return [
            InsertFileAction::make(
                disk: $this->getDisk(),
                multiple: $this->isMultiple(),
                acceptedCategories: $this->getAcceptedCategoryValues(),
            ),
        ];
    }
}
