<?php

namespace MmesDesign\FilamentFileManager\RichEditor\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use MmesDesign\FilamentFileManager\Enums\FileCategory;
use MmesDesign\FilamentFileManager\Services\FileManagerService;
use MmesDesign\FilamentFileManager\Services\FileTypeResolver;

class InsertFileAction
{
    /**
     * @param  array<int, string>  $acceptedCategories
     */
    public static function make(
        ?string $disk = null,
        bool $multiple = true,
        array $acceptedCategories = [],
    ): Action {
        $disk = $disk ?: config('filament-file-manager.disk', 'public');

        return Action::make('fileManager')
            ->label(__('filament-file-manager::file-manager.editors.file_manager'))
            ->slideOver()
            ->modalHeading(__('filament-file-manager::file-manager.editors.select_files'))
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->modalContent(fn (RichEditor $component): View => view(
                'filament-file-manager::rich-editor.file-picker-modal',
                [
                    'fieldId' => 'rich-editor-fm-' . $component->getKey(),
                    'multiple' => $multiple,
                    'disk' => $disk,
                    'acceptedCategories' => $acceptedCategories,
                ],
            ))
            ->action(function (array $arguments, RichEditor $component) use ($disk): void {
                $paths = json_decode($arguments['selectedPaths'] ?? '[]', true);

                if (! is_array($paths)) {
                    $paths = [$paths];
                }

                $paths = array_values(array_filter($paths));

                if ($paths === []) {
                    return;
                }

                $fileManagerService = app(FileManagerService::class);
                $fileTypeResolver = app(FileTypeResolver::class);
                $commands = [];
                $skippedCount = 0;

                foreach ($paths as $path) {
                    $url = $fileManagerService->getUrl($disk, $path);

                    if ($url === null) {
                        $skippedCount++;

                        continue;
                    }

                    $name = basename($path);
                    $category = $fileTypeResolver->resolve($name);

                    if ($category === FileCategory::Image) {
                        $commands[] = EditorCommand::make('insertContent', arguments: [[
                            'type' => 'image',
                            'attrs' => [
                                'src' => $url,
                                'alt' => $name,
                            ],
                        ]]);
                    } else {
                        $commands[] = EditorCommand::make('insertContent', arguments: [[
                            'type' => 'text',
                            'marks' => [
                                [
                                    'type' => 'link',
                                    'attrs' => [
                                        'href' => $url,
                                        'target' => '_blank',
                                    ],
                                ],
                            ],
                            'text' => $name,
                        ]]);
                    }
                }

                if ($commands !== []) {
                    $component->runCommands(
                        $commands,
                        editorSelection: $arguments['editorSelection'] ?? null,
                    );
                }

                if ($skippedCount > 0) {
                    Notification::make()
                        ->warning()
                        ->title(__('filament-file-manager::file-manager.editors.no_url_warning'))
                        ->send();
                }
            });
    }
}
