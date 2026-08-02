<?php

use Dowhile\FilamentTweaks\Contracts\ShowsRelationManagers;
use Dowhile\FilamentTweaks\FilamentTweaksServiceProvider;
use Dowhile\FilamentTweaks\Livewire\HidesRelationManagersOnEditPages;
use Filament\Resources\Pages\EditRecord;
use Livewire\ComponentHookRegistry;

function bootHookOn(EditRecord $page): EditRecord
{
    $hook = new HidesRelationManagersOnEditPages;
    $hook->setComponent($page);
    $hook->boot();

    return $page;
}

function cachedRelationManagers(EditRecord $page): ?array
{
    return (fn () => $this->cachedRelationManagers)->call($page);
}

it('is disabled by default', function () {
    expect(config('filament-tweaks.features.hide_relation_managers_on_edit_pages'))->toBeFalse();
});

it('registers the hook when the feature is enabled', function () {
    config()->set('filament-tweaks.features.hide_relation_managers_on_edit_pages', true);

    (new FilamentTweaksServiceProvider(app()))->packageBooted();

    $hooks = array_map(
        fn ($hook) => is_object($hook) ? $hook::class : $hook,
        (new ReflectionProperty(ComponentHookRegistry::class, 'componentHooks'))->getValue(),
    );

    expect($hooks)->toContain(HidesRelationManagersOnEditPages::class);
});

it('empties the relation managers of a plain edit page', function () {
    $page = bootHookOn(new class extends EditRecord {});

    expect(cachedRelationManagers($page))->toBe([]);
});

it('leaves a page that declares its own relation managers alone', function () {
    $page = bootHookOn(new class extends EditRecord
    {
        public function getRelationManagers(): array
        {
            return ['manager'];
        }
    });

    expect(cachedRelationManagers($page))->toBeNull()
        ->and($page->getRelationManagers())->toBe(['manager']);
});

it('leaves a page implementing ShowsRelationManagers alone', function () {
    $page = bootHookOn(new class extends EditRecord implements ShowsRelationManagers {});

    expect(cachedRelationManagers($page))->toBeNull();
});

it('ignores components that are not edit pages', function () {
    $hook = new HidesRelationManagersOnEditPages;
    $hook->setComponent(new class extends Livewire\Component
    {
        public function render()
        {
            return '<div></div>';
        }
    });

    $hook->boot();
})->throwsNoExceptions();
