<?php

use Dowhile\FilamentTweaks\FilamentTweaksServiceProvider;
use Dowhile\FilamentTweaks\Macros;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

function flushTweakMacros(): void
{
    Table::flushMacros();
    TextInput::flushMacros();
    Textarea::flushMacros();
}

function tableWithColumns(array $columns): Table
{
    return Table::make(Mockery::mock(HasTable::class))->columns($columns);
}

// Macros are static state on the component classes and survive between tests, so they are
// re-registered before each test; whatever asserts on registration flushes them first.
beforeEach(fn () => Macros::register());

it('registers the macros from the service provider, with no panel booted', function () {
    flushTweakMacros();

    (new FilamentTweaksServiceProvider(app()))->packageRegistered();

    expect(Table::hasMacro('allColumnsToggleable'))->toBeTrue()
        ->and(TextInput::hasMacro('currencyMask'))->toBeTrue()
        ->and(Textarea::hasMacro('autogrow'))->toBeTrue();
});

it('can be registered again without failing', function () {
    Macros::register();
    Macros::register();

    expect(Table::hasMacro('allColumnsToggleable'))->toBeTrue();
});

it('skips the macros their feature flag disables', function (string $feature, string $class, string $macro) {
    flushTweakMacros();

    config()->set("filament-tweaks.features.{$feature}", false);

    Macros::register();

    expect($class::hasMacro($macro))->toBeFalse();
})->with([
    ['enable_all_columns_toggleable', Table::class, 'allColumnsToggleable'],
    ['enable_currency_mask', TextInput::class, 'currencyMask'],
    ['enable_autogrow_textarea', Textarea::class, 'autogrow'],
]);

it('makes every column toggleable, keeping its own default visibility', function () {
    $table = tableWithColumns([
        TextColumn::make('name'),
        TextColumn::make('notes')->toggleable(isToggledHiddenByDefault: true),
    ])->allColumnsToggleable();

    $columns = $table->getColumns();

    expect($columns['name']->isToggleable())->toBeTrue()
        ->and($columns['name']->isToggledHiddenByDefault())->toBeFalse()
        ->and($columns['notes']->isToggleable())->toBeTrue()
        ->and($columns['notes']->isToggledHiddenByDefault())->toBeTrue()
        ->and($table->getColumnManagerMaxHeight())->toBe('500px');
});

it('leaves the table untouched when the condition is false', function ($condition) {
    $table = tableWithColumns([TextColumn::make('name')])
        ->allColumnsToggleable($condition);

    expect($table->getColumns()['name']->isToggleable())->toBeFalse()
        ->and($table->getColumnManagerMaxHeight())->toBeNull();
})->with([
    'boolean' => false,
    'closure' => fn () => fn () => false,
]);

it('keeps a column manager height that was already set', function () {
    $table = tableWithColumns([TextColumn::make('name')])
        ->columnManagerMaxHeight('20rem')
        ->allColumnsToggleable();

    expect($table->getColumnManagerMaxHeight())->toBe('20rem');
});
