# Filament File Manager

A file manager plugin for [Filament](https://filamentphp.com). Browse, upload, rename, move, and delete files directly from your admin panel.

![Filament File Manager](https://raw.githubusercontent.com/marcomessa/filament-file-manager/main/art/screenshot.png)

## Features

- Grid and list view with sorting (name, size, date, type)
- Upload files with size and batch limits
- Create, rename, move, and delete files and folders
- Bulk operations (delete, move) with multi-select
- File preview for images, video, audio, code, and documents
- Automatic thumbnail generation for images
- `FilePicker` form component for selecting files in your resources
- Breadcrumb navigation
- Keyboard shortcuts (Ctrl+A, Delete, F2, Escape)
- Dark mode support
- Translations (English, Italian)
- Security: path sanitization and blocked dangerous extensions

## Requirements

- PHP 8.2+
- Laravel 11+
- Filament 5+

## Installation

```bash
composer require mmes-design/filament-file-manager
```

Register the plugin in your panel provider:

```php
use MmesDesign\FilamentFileManager\FileManagerPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FileManagerPlugin::make(),
        ]);
}
```

Publish the assets:

```bash
php artisan filament:assets
```

Publish the configuration (optional):

```bash
php artisan vendor:publish --tag=filament-file-manager-config
```

## Configuration

### Plugin options

```php
FileManagerPlugin::make()
    ->defaultDisk('public')
    ->navigationGroup('Content')
    ->navigationIcon('heroicon-o-folder')
    ->navigationSort(5)
```

### Disk

This package supports local disks only (`local`, `public`). Remote disks (S3, FTP, etc.) are available in the Pro version.

### Config file

The published config file (`config/filament-file-manager.php`) lets you customize:

```php
return [
    // Default filesystem disk
    'disk' => 'public',

    // Blocked file extensions
    'denied_extensions' => [
        'php', 'exe', 'bat', 'sh', 'bash', // ...
    ],

    // Upload limits
    'max_upload_size' => 50 * 1024,    // 50 MB in KB
    'max_uploads_per_batch' => 20,

    // Thumbnail settings
    'thumbnails' => [
        'enabled' => true,
        'directory' => '.thumbnails',
        'width' => 200,
        'height' => 200,
        'quality' => 80,
    ],
];
```

## Usage

### File Manager page

The plugin registers a File Manager page in your panel navigation automatically. It includes folder navigation, breadcrumbs, upload, bulk operations, preview, and sorting.

### FilePicker form component

Use `FilePicker` in your Filament resources to let users select files:

```php
use MmesDesign\FilamentFileManager\Forms\Components\FilePicker;

FilePicker::make('document')
```

#### Multiple selection

```php
FilePicker::make('attachments')
    ->multiple()
```

#### Filter by file category

```php
use MmesDesign\FilamentFileManager\Enums\FileCategory;

FilePicker::make('photo')
    ->acceptedCategories([FileCategory::Image])

FilePicker::make('media')
    ->acceptedCategories([FileCategory::Image, FileCategory::Video])
```

Available categories: `Image`, `Document`, `Audio`, `Video`, `Archive`, `Code`, `Other`.

#### Image preview

When the only accepted category is `Image`, thumbnail preview is enabled automatically. You can also enable it manually:

```php
FilePicker::make('cover')
    ->imagePreview()
```

#### Specific disk

```php
FilePicker::make('private_doc')
    ->disk('local')
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
