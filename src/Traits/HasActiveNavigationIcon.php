<?php

namespace Dowhile\FilamentTweaks\Traits;

trait HasActiveNavigationIcon
{
    public static function getActiveNavigationIcon(): ?string
    {
        return 'heroicon-'.str(self::getNavigationIcon()->value)
            ->replace('o-', 's-')
            ->toString();
    }
}
