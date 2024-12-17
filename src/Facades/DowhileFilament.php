<?php

namespace Dowhile\FilamentTweaks\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Dowhile\FilamentTweaks\DowhileFilament
 */
class DowhileFilament extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Dowhile\FilamentTweaks\DowhileFilament::class;
    }
}
