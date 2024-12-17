<?php

namespace Dowhile\FilamentTweaks\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Dowhile\FilamentTweaks\FilamentTweaks
 */
class FilamentTweaks extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Dowhile\FilamentTweaks\FilamentTweaks::class;
    }
}
