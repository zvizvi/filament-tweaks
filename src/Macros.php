<?php

namespace Dowhile\FilamentTweaks;

use Closure;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\Components\ComponentManager;
use Filament\Support\RawJs;
use Filament\Tables\Columns\Column;
use Filament\Tables\Table;
use WeakMap;

/**
 * Macros are global state on the component classes, not panel state, so they are
 * registered from the service provider instead of from the plugin.
 *
 * A plugin's boot() only runs once a panel boots, and a panel only boots on a request
 * that passes through Filament's SetUpPanel middleware — so in tests, queued jobs and
 * console commands the macros would simply not exist ("Method ... does not exist"),
 * depending on whether something happened to hit a panel route first.
 */
class Macros
{
    /**
     * The component managers a column-level default has already been registered on.
     *
     * @var WeakMap<object, true>|null
     */
    protected static ?WeakMap $columnDefaultManagers = null;

    /**
     * Register every macro the configuration enables. Safe to call more than once.
     */
    public static function register(): void
    {
        static::registerAllColumnsToggleable();
        static::registerCurrencyMask();
        static::registerAutogrow();
    }

    protected static function registerAllColumnsToggleable(): void
    {
        if (! config('filament-tweaks.features.enable_all_columns_toggleable', true)) {
            return;
        }

        if (Table::hasMacro('allColumnsToggleable')) {
            return;
        }

        Table::macro('allColumnsToggleable', function (bool|Closure $condition = true) {
            /** @var Table $this */
            if (! $this->evaluate($condition)) {
                return $this;
            }

            $columns = $this->getColumns();

            // No columns yet means the macro was reached BEFORE ->columns([...]),
            // which is what happens when it is used as a panel-wide default from
            // Table::configureUsing(): Filament runs those callbacks inside
            // Table::make(), and a resource chains its columns on afterwards.
            // There is nothing to mutate at that point, so the default is turned
            // on one level down instead of silently doing nothing.
            if ($columns === []) {
                // Named in full: Macroable binds the closure's scope to the Table
                // class, so static:: here would look for the method on Filament's.
                Macros::toggleColumnsByDefault();
            }

            foreach ($columns as $column) {
                /** @var Column $column */
                $column->toggleable(isToggledHiddenByDefault: $column->isToggledHiddenByDefault());
            }

            $this->columnManagerMaxHeight($this->getColumnManagerMaxHeight() ?? '500px');

            return $this;
        });
    }

    /**
     * Make every column built from here on toggleable.
     *
     * Column::configureUsing() runs inside Column::make(), i.e. before the
     * column's own chain, so a column that goes on to declare
     * ->toggleable(isToggledHiddenByDefault: true) - or ->toggleable(false) -
     * still has the last word, exactly as it does when the macro mutates
     * columns that are already built.
     *
     * Registered once per REQUEST, keyed by the component manager the
     * configuration is written to: Filament scopes that manager per request, so
     * a long-lived worker (Octane) is handed a fresh one - and a plain static
     * "already done" flag would leave every request after the first with no
     * default at all.
     */
    public static function toggleColumnsByDefault(): void
    {
        $manager = ComponentManager::resolve();

        static::$columnDefaultManagers ??= new WeakMap;

        if (isset(static::$columnDefaultManagers[$manager])) {
            return;
        }

        static::$columnDefaultManagers[$manager] = true;

        Column::configureUsing(fn (Column $column) => $column->toggleable());
    }

    protected static function registerCurrencyMask(): void
    {
        if (! config('filament-tweaks.features.enable_currency_mask', true)) {
            return;
        }

        if (TextInput::hasMacro('currencyMask')) {
            return;
        }

        TextInput::macro('currencyMask', function (): TextInput {
            /** @var TextInput $this */
            return $this->numeric()
                ->mask(RawJs::make('$money($input)'))
                ->stripCharacters(',')
                ->extraInputAttributes([
                    'maxlength' => '12',
                ]);
        });
    }

    protected static function registerAutogrow(): void
    {
        if (! config('filament-tweaks.features.enable_autogrow_textarea', true)) {
            return;
        }

        if (Textarea::hasMacro('autogrow')) {
            return;
        }

        Textarea::macro('autogrow', function ($maxHeight = null): Textarea {
            /** @var Textarea $this */
            $attributes = ['class' => 'autogrow'];
            if ($maxHeight) {
                $attributes['style'] = 'max-height:'.$maxHeight;
            }

            return $this->extraInputAttributes($attributes);
        });
    }
}
