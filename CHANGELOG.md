# Changelog

All notable changes to this project will be documented in this file.

## v1.1.0 - 2026-03-01

### Added
- Folder tree sidebar with hierarchical navigation, expand/collapse per node, and sync with current path
- "Check for updates" header action — queries Packagist API and notifies if a new version is available
- Full-height page layout with collapsible sidebars (folder tree and file preview)

### Changed
- Removed `spatie/laravel-package-tools` dependency — service provider rewritten with plain Laravel
- Updated compiled CSS asset for TailwindCSS v4.2.0
- Replaced hardcoded strings in tests with translation keys

### Fixed
- Spacing inconsistency in `Css::make` method in FileManagerPlugin

## v1.0.3 - 2026-02-25

- Fix FilePicker confirm selection not setting value on the form field
- Fix `unmountFormComponentAction` not found error by using `unmountAction`

## v1.0.2 - 2026-02-25

- Fix Alpine expression error "previewFile is not defined" in FilePicker modal

## v1.0.0 - 2026-02-21

- Initial release
- File Manager page with grid and list views
- Upload, rename, move, and delete files and folders
- Bulk operations (delete, move)
- File preview for images, video, audio, code, and documents
- Automatic thumbnail generation
- FilePicker form component
- Breadcrumb navigation
- Sorting by name, size, date, and type
- Keyboard shortcuts
- Dark mode support
- English and Italian translations
