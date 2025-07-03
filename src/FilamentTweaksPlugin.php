<?php

namespace Dowhile\FilamentTweaks;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\AssociateAction;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Contracts\Plugin;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Entry;
use Filament\Pages\BasePage;
use Filament\Panel;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Alignment;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\RawJs;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FilamentTweaksPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-tweaks';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        $panel
            ->sidebarCollapsibleOnDesktop()
            ->brandName(env('APP_NAME'))
            ->resourceCreatePageRedirect('view')
            ->resourceEditPageRedirect('view');

        // Disable default readOnlyRelationManagersOnResourceViewPagesByDefault
        if (config('filament-tweaks.features.disable_readonly_relation_managers', true)) {
            $panel->readOnlyRelationManagersOnResourceViewPagesByDefault(false);
        }

        // Centering form actions
        if (config('filament-tweaks.features.center_form_actions', true)) {
            BasePage::$formActionsAlignment = Alignment::Center;
            Action::configureUsing(function (Action $action) {
                $action->modalFooterActionsAlignment(Alignment::Center);
            });
        }

        // Disable CreateAndCreateAnother
        if (config('filament-tweaks.features.disable_create_another', true)) {
            CreateRecord::disableCreateAnother();
            CreateAction::configureUsing(fn (CreateAction $action) => $action->createAnother(false));
            AttachAction::configureUsing(fn (AttachAction $action) => $action->attachAnother(false));
            AssociateAction::configureUsing(fn (AssociateAction $action) => $action->associateAnother(false));
        }

        // Set translateLabel for all actions
        if (config('filament-tweaks.features.translate_labels', true)) {
            $components = [
                BaseFilter::class,
                Column::class,
                Field::class,
                Entry::class,
                Action::class,
                ActionGroup::class,
                Tab::class,
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
                    ->reorderableColumns()
                    ->deferFilters(false)
                    ->deferColumnManager(false)
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

        // Make all columns toggleable
        if (config('filament-tweaks.features.enable_all_columns_toggleable', true)) {
            Table::macro('allColumnsToggleable', function () {
                /** @var Table $this */
                $columns = $this->getColumns();
                foreach ($columns as $column) {
                    /** @var Column $column */
                    $column->toggleable(isToggledHiddenByDefault: $column->isToggledHiddenByDefault());
                }

                $this->columnManagerMaxHeight($this->getColumnManagerMaxHeight() ?? '500px');

                return $this;
            });
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

        if (config('filament-tweaks.features.enable_autogrow_textarea', true)) {
            Textarea::macro('autogrow', function (): Textarea {
                return $this->extraInputAttributes(['class' => 'autogrow']);
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
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
