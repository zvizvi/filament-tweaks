<?php

namespace Dowhile\FilamentTweaks;

use Dowhile\FilamentTweaks\Commands\FilamentTweaksCommand;
use Dowhile\FilamentTweaks\Testing\TestsFilamentTweaks;
use Filament\Facades\Filament;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentTweaksServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-tweaks';

    public static string $viewNamespace = 'filament-tweaks';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('zvizvi/filament-tweaks');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        $panels = Filament::getPanels();
        foreach ($panels as $panel) {
            $panel->spa();
        }

        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__.'/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/filament-tweaks/{$file->getFilename()}"),
                ], 'filament-tweaks-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsFilamentTweaks);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'dowhile/filament-tweaks';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        $assets = [
            // AlpineComponent::make('filament-tweaks', __DIR__ . '/../resources/dist/components/filament-tweaks.js'),
            Css::make('filament-tweaks-styles', __DIR__.'/../resources/dist/filament-tweaks.css'),
        ];

        if (config('filament-tweaks.features.configure_date_range_picker', true)) {
            $assets[] = Css::make('dowhile-filament-tweaks-daterangepicker-styles', __DIR__.'/../resources/css/date-range-picker.css');
        }

        return $assets;
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            FilamentTweaksCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_filament-tweaks_table',
        ];
    }
}
