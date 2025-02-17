<?php

namespace Dowhile\FilamentTweaks\Traits;

trait HasActiveNavigationIcon
{
    public static function getActiveNavigationIcon(): ?string
    {
        return str(self::getNavigationIcon())
            ->replace('heroicon-o', 'heroicon-s')
            ->toString();
    }
}
