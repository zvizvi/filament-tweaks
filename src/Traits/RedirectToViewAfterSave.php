<?php

namespace Dowhile\FilamentTweaks\Traits;

trait RedirectToViewAfterSave
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
