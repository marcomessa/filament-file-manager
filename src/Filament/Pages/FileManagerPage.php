<?php

namespace MmesDesign\FilamentFileManager\Filament\Pages;

use Composer\InstalledVersions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Http;
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

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('checkForUpdates')
                ->label(__('filament-file-manager::file-manager.actions.check_updates'))
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function (): void {
                    $currentVersion = $this->getInstalledVersion();

                    try {
                        $response = Http::timeout(10)
                            ->get('https://repo.packagist.org/p2/mmes-design/filament-file-manager.json');

                        $latestVersion = $response->json('packages.mmes-design/filament-file-manager.0.version');

                        $current = ltrim($currentVersion, 'v');
                        $latest = ltrim($latestVersion, 'v');

                        if (version_compare($current, $latest, '>=')) {
                            Notification::make()
                                ->success()
                                ->title(__('filament-file-manager::file-manager.actions.up_to_date_title'))
                                ->body(__('filament-file-manager::file-manager.actions.up_to_date_body', ['current' => $currentVersion]))
                                ->send();
                        } else {
                            Notification::make()
                                ->warning()
                                ->title(__('filament-file-manager::file-manager.actions.update_available_title'))
                                ->body(__('filament-file-manager::file-manager.actions.update_available_body', ['latest' => $latestVersion, 'current' => $currentVersion]))
                                ->send();
                        }
                    } catch (\Throwable) {
                        Notification::make()
                            ->danger()
                            ->title(__('filament-file-manager::file-manager.actions.update_check_failed_title'))
                            ->body(__('filament-file-manager::file-manager.actions.update_check_failed_body'))
                            ->send();
                    }
                }),
        ];
    }

    protected function getInstalledVersion(): ?string
    {
        return InstalledVersions::getPrettyVersion('mmes-design/filament-file-manager');
    }
}
