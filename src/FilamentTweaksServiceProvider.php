<?php

namespace Dowhile\FilamentTweaks;

use Dowhile\FilamentTweaks\Commands\FilamentTweaksCommand;
use Dowhile\FilamentTweaks\Testing\TestsFilamentTweaks;
use Filament\Actions\CreateAction;
use Filament\Actions\MountableAction;
use Filament\Actions\StaticAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Entry;
use Filament\Infolists\Components\Tabs\Tab as InfolistTab;
use Filament\Pages\BasePage;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Enums\Alignment;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\RawJs;
use Filament\Tables\Actions\AssociateAction;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\CreateAction as TablesCreateAction;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
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
        // Disable default readOnlyRelationManagersOnResourceViewPagesByDefault
        if (config('filament-tweaks.features.disable_readonly_relation_managers', true)) {
            $panels = Filament::getPanels();
            foreach ($panels as $panel) {
                $panel->readOnlyRelationManagersOnResourceViewPagesByDefault(false);
            }
        }

        // Centering form actions
        if (config('filament-tweaks.features.center_form_actions', true)) {
            BasePage::$formActionsAlignment = Alignment::Center;
            MountableAction::configureUsing(function (MountableAction $action) {
                $action->modalFooterActionsAlignment(Alignment::Center);
            });
        }

        // Disable CreateAndCreateAnother
        if (config('filament-tweaks.features.disable_create_another', true)) {
            CreateRecord::disableCreateAnother();
            CreateAction::configureUsing(fn (CreateAction $action) => $action->createAnother(false));
            TablesCreateAction::configureUsing(fn (TablesCreateAction $action) => $action->createAnother(false));
            AttachAction::configureUsing(fn (AttachAction $action) => $action->attachAnother(false));
            AssociateAction::configureUsing(fn (AssociateAction $action) => $action->associateAnother(false));
        }

        // Set translateLabel for all actions
        if (config('filament-tweaks.features.translate_labels', true)) {
            $components = [
                BaseFilter::class,
                Column::class,
                Entry::class,
                Field::class,
                StaticAction::class,
                Placeholder::class,
                Tab::class,
                InfolistTab::class,
            ];
            foreach ($components as $component) {
                $component::configureUsing(function ($c): void {
                    $c->translateLabel();
                });
            }
        }

        // Not native select
        if (config('filament-tweaks.features.non_native_select', true)) {
            Select::configureUsing(function (Select $select): void {
                $select->native(false);
            });
            SelectFilter::configureUsing(function (SelectFilter $filter): void {
                $filter->native(false);
            });
        }

        // Date time picker without seconds and week starts on sunday
        if (config('filament-tweaks.features.configure_datetime_picker', true)) {
            DateTimePicker::configureUsing(function (DateTimePicker $dateTimePicker): void {
                $dateTimePicker
                    ->seconds(false)
                    ->weekStartsOnSunday();
            });
        }

        // Table style
        if (config('filament-tweaks.features.configure_table_styling', true)) {
            Table::configureUsing(function (Table $table): void {
                $table
                    ->striped()
                    ->defaultPaginationPageOption(25);
            });
        }

        // Customize system icons
        if (config('filament-tweaks.features.customize_system_icons', false)) {
            FilamentIcon::register([
                'panels::sidebar.collapse-button' => 'heroicon-o-bars-3-bottom-right',
                'panels::sidebar.collapse-button.rtl' => 'heroicon-o-bars-3-bottom-left',
                'panels::sidebar.expand-button' => 'heroicon-o-bars-3',
                'panels::sidebar.expand-button.rtl' => 'heroicon-o-bars-3',
            ]);
        }

        // Currency mask for text inputs
        if (config('filament-tweaks.features.enable_currency_mask', true)) {
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
        }

        // Configure plugins

        // DateRangeFilter configuration
        if (config('filament-tweaks.features.configure_date_range_picker', true)) {
            $dateRangeFilterClass = 'Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter';

            if (class_exists($dateRangeFilterClass)) {
                $dateRangeFilterClass::configureUsing(function ($datePicker) {
                    $datePicker
                        ->firstDayOfWeek(7)
                        ->autoApply(true)
                        ->icon('heroicon-o-calendar');
                });
            }
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
            Js::make('filament-tweaks-scripts', __DIR__.'/../resources/dist/filament-tweaks.js'),
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
