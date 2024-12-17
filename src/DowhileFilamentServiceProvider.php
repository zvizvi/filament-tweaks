<?php

namespace Dowhile\FilamentTweaks;

use Dowhile\FilamentTweaks\Commands\DowhileFilamentCommand;
use Dowhile\FilamentTweaks\Testing\TestsDowhileFilament;
use Filament\Actions\CreateAction;
use Filament\Actions\MountableAction;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Entry;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Enums\Alignment;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\RawJs;
use Filament\Tables\Actions\Action as TablesAction;
use Filament\Tables\Actions\CreateAction as TablesCreateAction;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Table;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DowhileFilamentServiceProvider extends PackageServiceProvider
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
        // Centering form actions
        CreateRecord::$formActionsAlignment = Alignment::Center;
        EditRecord::$formActionsAlignment = Alignment::Center;
        Action::configureUsing(function (Action $action): void {
            $action->modalFooterActionsAlignment(Alignment::Center);
        });
        TablesAction::configureUsing(function (TablesAction $action) {
            $action->modalFooterActionsAlignment(Alignment::Center);
        });
        MountableAction::configureUsing(function (MountableAction $action) {
            $action->modalFooterActionsAlignment(Alignment::Center);
        });

        // Disable CreateAndCreateAnother
        CreateRecord::disableCreateAnother();
        CreateAction::configureUsing(fn (CreateAction $action) => $action->createAnother(false));
        TablesCreateAction::configureUsing(fn (TablesCreateAction $action) => $action->createAnother(false));

        // Set translateLabel for all actions
        Action::configureUsing(function (Action $action): void {
            $action->translateLabel();
        });
        TablesAction::configureUsing(function (TablesAction $action) {
            $action->translateLabel();
        });
        Column::configureUsing(function (Column $column): void {
            $column->translateLabel();
        });
        BaseFilter::configureUsing(function (BaseFilter $filter): void {
            $filter->translateLabel();
        });
        Field::configureUsing(function (Field $field): void {
            $field->translateLabel();
        });
        Entry::configureUsing(function (Entry $entry): void {
            $entry->translateLabel();
        });

        // Table style
        Table::configureUsing(function (Table $table): void {
            $table
                ->striped()
                ->defaultPaginationPageOption(25);
        });

        // Macros
        TextInput::macro('currencyMask', function (): TextInput {
            /**
             * @var TextInput $this
             */
            return $this->numeric()
                ->mask(RawJs::make('$money($input)'))
                ->stripCharacters(',')
                ->extraInputAttributes([
                    'maxlength' => '12',
                ]);
        });

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
        Testable::mixin(new TestsDowhileFilament);
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
        return [
            // AlpineComponent::make('filament-tweaks', __DIR__ . '/../resources/dist/components/filament-tweaks.js'),
            Css::make('filament-tweaks-styles', __DIR__.'/../resources/dist/filament-tweaks.css'),
            Js::make('filament-tweaks-scripts', __DIR__.'/../resources/dist/filament-tweaks.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            DowhileFilamentCommand::class,
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
