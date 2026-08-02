<?php

namespace Dowhile\FilamentTweaks\Livewire;

use Dowhile\FilamentTweaks\Contracts\ShowsRelationManagers;
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

        if (static::declaresOwnRelationManagers($page)) {
            return;
        }

        // `getCachedRelationManagers()` only calls `getRelationManagers()` when the
        // cache is still null, so priming it with an empty array is enough.
        (function (): void {
            $this->cachedRelationManagers = [];
        })->call($page);
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
