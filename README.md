# Filament Tweaks

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dowhile/filament-tweaks.svg?style=flat-square)](https://packagist.org/packages/dowhile/filament-tweaks)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/zvizvi/filament-tweaks/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/zvizvi/filament-tweaks/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/zvizvi/filament-tweaks/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/zvizvi/filament-tweaks/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/dowhile/filament-tweaks.svg?style=flat-square)](https://packagist.org/packages/dowhile/filament-tweaks)

An opinionated collection of defaults for Filament panels — the settings you would otherwise copy into every project by hand.

It applies sensible global defaults (non-native selects, striped tables, centered form actions, translated labels, date/time and currency formats), ships a handful of macros and reusable filters, and includes CSS fixes for RTL panels. Every tweak is a config flag, so you can turn off anything you disagree with.

```php
// config/filament-tweaks.php
'features' => [
    'non_native_select' => true,   // Select::native(false) everywhere
    'center_form_actions' => false, // ...but keep form actions where they are
],
```

## Installation

Install the package via composer:

```bash
composer require dowhile/filament-tweaks
```

Register the plugin on any panel you want it applied to:

```php
use Dowhile\FilamentTweaks\FilamentTweaksPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(FilamentTweaksPlugin::make());
}
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag="filament-tweaks-config"
```

## Panel defaults

These are applied to every panel the plugin is registered on, and are not configurable:

- `brandName()` falls back to `APP_NAME` when the panel does not set one.
- `resourceCreatePageRedirect('view')` and `resourceEditPageRedirect('view')` — after create/edit, land on the view page.
- SPA mode (`$panel->spa()`) is enabled on **all** registered panels by the service provider.

If [joaopaulolndev/filament-edit-profile](https://github.com/joaopaulolndev/filament-edit-profile) is installed, its `edit_profile_form`, `edit_password_form` and `browser_sessions_form` Livewire components are aliased so they can be rendered by name.

## Features

Each feature is toggled by a key under `features` in `config/filament-tweaks.php`.

| Key | Default | What it does |
| --- | --- | --- |
| `sidebar_collapsible_on_desktop` | `true` | Enables `sidebarCollapsibleOnDesktop()`. |
| `disable_readonly_relation_managers` | `true` | Relation managers on view pages stay editable. |
| `center_form_actions` | `true` | Centers form actions and modal footer actions. |
| `disable_create_another` | `true` | Removes "create another" from `CreateRecord`, `CreateAction`, `AttachAction` and `AssociateAction`. |
| `translate_labels` | `true` | Calls `translateLabel()` on actions, columns, fields, entries, filters, sections, tabs, steps and groups; also runs section and empty-state headings through `__()`. |
| `non_native_select` | `true` | `Select` and `SelectFilter` use `native(false)`. |
| `configure_datetime_picker` | `true` | `DateTimePicker` without seconds, week starting on Sunday. |
| `configure_table_styling` | `true` | Tables are striped, columns reorderable, filters and column manager applied immediately, 25 rows per page. |
| `enable_rich_editor_toolbar_buttons` | `true` | A fuller `RichEditor` toolbar (headings, alignment, sub/superscript, tables, code blocks, undo/redo). |
| `customize_system_icons` | `false` | Swaps the sidebar collapse/expand icons for `heroicon-o-bars-3*` variants. |
| `enable_all_columns_toggleable` | `true` | Registers the `allColumnsToggleable()` table macro. |
| `enable_currency_mask` | `true` | Registers the `currencyMask()` text input macro. |
| `enable_autogrow_textarea` | `true` | Registers the `autogrow()` textarea macro. |
| `configure_date_range_picker` | `true` | Configures [malzariey/filament-daterangepicker-filter](https://github.com/malzariey/filament-daterangepicker-filter) when installed, and loads its RTL stylesheet. |

## Formats

Uncomment any of these to set panel-wide display defaults. Each one is skipped when left unset:

```php
'formats' => [
    'date' => 'd/m/Y',
    'time' => 'H:i:s',
    'datetime' => 'd/m/Y H:i:s',
    'currency' => 'ils',
    'timezone' => 'Asia/Jerusalem',
],
```

`date`, `time`, `datetime` and `currency` are applied to both `Table` and `Schema` defaults; `timezone` is passed to `FilamentTimezone::set()`.

## Macros

```php
// Make every column toggleable, keeping each column's own default visibility.
// Also caps the column manager height at 500px unless you set your own.
$table->columns([...])->allColumnsToggleable();

// Numeric input with a thousands-separator money mask, capped at 12 characters.
TextInput::make('price')->currencyMask();

// Textarea that grows with its content (via CSS `field-sizing`), with an optional max height.
Textarea::make('notes')->autogrow('20rem');
```

## Filters

```php
use Dowhile\FilamentTweaks\Filters\RangeFilter;
use Dowhile\FilamentTweaks\Filters\TextFilter;

// A single text input filtering the column with `like %value%`.
TextFilter::make('name');

// Any operator you like, and an optional numeric input.
TextFilter::make('code', '=');
TextFilter::make('quantity')->operator('>=')->numeric();

// Min/max inputs filtering the column with `>=` and `<=`.
RangeFilter::make('price');
```

Both filters render an active indicator built from the filter label. Note that `RangeFilter` currently uses hard-coded Hebrew labels for its min/max inputs.

## Navigation icons

`HasActiveNavigationIcon` derives the active icon from the regular one by swapping the outline heroicon for its solid variant (`heroicon-o-users` → `heroicon-s-users`):

```php
use Dowhile\FilamentTweaks\Traits\HasActiveNavigationIcon;

class UserResource extends Resource
{
    use HasActiveNavigationIcon;

    protected static ?string $navigationIcon = 'heroicon-o-users';
}
```

`Dowhile\FilamentTweaks\Pages\Dashboard` is a drop-in Filament dashboard with the trait already applied.

## Styling

A stylesheet is registered automatically with the panel. It covers:

- `[x-cloak]` hiding, and `.dir-rtl` / `.dir-ltr` direction utilities.
- Centered modal footer actions on larger screens.
- Autogrowing textareas (`textarea.autogrow`).
- A single-tenant tenant menu rendered without a dropdown icon or hover state.
- RTL fixes: LTR-aligned email/tel inputs and columns, a mirrored `nprogress` bar, button group borders, and flipped alignment/undo/redo icons in the rich editor.

When the date range picker filter package is installed, a second stylesheet fixes its RTL layout (calendar direction, arrows, ranges panel and buttons).

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [zvizvi](https://github.com/zvizvi)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
