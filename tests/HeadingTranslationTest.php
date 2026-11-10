<?php

use Dowhile\FilamentTweaks\FilamentTweaksPlugin;
use Filament\Panel;
use Filament\Schemas\Components\EmptyState;
use Filament\Schemas\Components\Section;

function bootTweaksPlugin(): void
{
    (new FilamentTweaksPlugin)->boot(Panel::make()->id('test'));
}

beforeEach(function () {
    app('translator')->addLines(['*.Details' => 'Translated details'], app()->getLocale());
});

it('translates a static heading', function (string $component) {
    bootTweaksPlugin();

    expect($component::make('Details')->getHeading())->toBe('Translated details');
})->with([
    'section' => Section::class,
    'empty state' => EmptyState::class,
]);

it('does not evaluate a closure heading while the component is being made', function (string $component) {
    bootTweaksPlugin();

    $evaluations = 0;

    $made = $component::make(function ($record) use (&$evaluations): string {
        $evaluations++;

        return $record->subject;
    });

    expect($made)->toBeInstanceOf($component)
        ->and($evaluations)->toBe(0);
})->with([
    'section' => Section::class,
    'empty state' => EmptyState::class,
]);

it('keeps a closure heading dynamic', function () {
    bootTweaksPlugin();

    $subject = 'First';

    $section = Section::make(function () use (&$subject): string {
        return $subject;
    });

    expect($section->getHeading())->toBe('First');

    $subject = 'Second';

    expect($section->getHeading())->toBe('Second');
});

it('leaves headings untranslated when the feature is disabled', function () {
    config()->set('filament-tweaks.features.translate_labels', false);

    bootTweaksPlugin();

    expect(Section::make('Details')->getHeading())->toBe('Details');
});
