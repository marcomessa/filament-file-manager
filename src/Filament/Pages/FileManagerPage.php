<?php

namespace MmesDesign\FilamentFileManager\Filament\Pages;

use Filament\Pages\Page;
use MmesDesign\FilamentFileManager\FileManagerPlugin;

class FileManagerPage extends Page
{
    protected string $view = 'filament-file-manager::pages.file-manager-page';

    protected static ?string $title = 'File Manager';

    protected static ?string $navigationLabel = 'File Manager';

    protected static ?string $slug = 'file-manager';

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return FileManagerPlugin::get()->getNavigationIcon();
    }

    public static function getNavigationGroup(): ?string
    {
        return FileManagerPlugin::get()->getNavigationGroup();
    }

    public static function getNavigationSort(): ?int
    {
        return FileManagerPlugin::get()->getNavigationSort();
    }
}
