<?php

namespace Dowhile\FilamentTweaks\Livewire;

use Dowhile\FilamentTweaks\Contracts\ShowsRelationManagers;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Livewire\ComponentHook;
use ReflectionMethod;

/**
 * Makes `getRelationManagers()` return an empty array on every edit page,
 * as if each page declared:
 *
 *     public function getRelationManagers(): array
 *     {
 *         return [];
 *     }
 *
 * Pages that declare their own `getRelationManagers()`, or implement
 * `ShowsRelationManagers`, are left untouched.
 *
 * Only applies to the panel handling the current request, and only when that
 * panel opted in — either through the plugin or through the config default.
 */
class HidesRelationManagersOnEditPages extends ComponentHook
{
    public function boot(): void
    {
        $page = $this->component;

        if (! $page instanceof EditRecord) {
            return;
        }

        if ($page instanceof ShowsRelationManagers) {
            return;
        }

        if (! static::isEnabledForCurrentPanel()) {
            return;
        }

        if (static::declaresOwnRelationManagers($page)) {
            return;
        }

        // `getCachedRelationManagers()` only calls `getRelationManagers()` when the
        // cache is still null, so priming it with an empty array is enough.
        (function (): void {
            $this->cachedRelationManagers = [];
        })->call($page);
    }

    /**
     * `true` covers every panel, an array limits it to the listed panel IDs.
     */
    protected static function isEnabledForCurrentPanel(): bool
    {
        $feature = config('filament-tweaks.features.hide_relation_managers_on_edit_pages', false);

        if (is_array($feature)) {
            return in_array(Filament::getCurrentPanel()?->getId(), $feature, strict: true);
        }

        return (bool) $feature;
    }

    protected static function declaresOwnRelationManagers(EditRecord $page): bool
    {
        $declaringClass = (new ReflectionMethod($page, 'getRelationManagers'))
            ->getDeclaringClass()
            ->getName();

        $inheritedFrom = (new ReflectionMethod(EditRecord::class, 'getRelationManagers'))
            ->getDeclaringClass()
            ->getName();

        return $declaringClass !== $inheritedFrom;
    }
}
