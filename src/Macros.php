<?php

namespace Dowhile\FilamentTweaks;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;
use Filament\Tables\Columns\Column;
use Filament\Tables\Table;

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
