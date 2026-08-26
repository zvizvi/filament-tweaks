<?php

/**
 * IDE stubs for the macros this package registers on Filament's classes.
 *
 * Macros are added at runtime, so editors have no way to know they exist. This file
 * declares them where the editor's indexer can see them: it is never autoloaded, and
 * every declaration sits behind `if (false)`, so PHP never runs any of it — VS Code
 * (Intelephense) and PhpStorm index the sources anyway and offer the completions.
 *
 * Keep the signatures here in sync with \Dowhile\FilamentTweaks\Macros.
 */

namespace Filament\Tables {
    if (false) {
        class Table
        {
            /**
             * Make every column toggleable, keeping each column's own default visibility,
             * and cap the column manager height at 500px unless one is already set.
             *
             * Pass a falsy condition to leave the table exactly as it was.
             */
            public function allColumnsToggleable(bool | \Closure $condition = true): static {}
        }
    }
}

namespace Filament\Forms\Components {
    if (false) {
        class TextInput
        {
            /**
             * Numeric input with a thousands-separator money mask, capped at 12 characters.
             */
            public function currencyMask(): static {}
        }

        class Textarea
        {
            /**
             * Textarea that grows with its content (via CSS `field-sizing`).
             *
             * @param  string|null  $maxHeight  Any CSS length, e.g. `'20rem'`.
             */
            public function autogrow(?string $maxHeight = null): static {}
        }
    }
}
