<?php

use Dowhile\FilamentTweaks\Contracts\ShowsRelationManagers;
use Dowhile\FilamentTweaks\FilamentTweaksServiceProvider;
use Dowhile\FilamentTweaks\Livewire\HidesRelationManagersOnEditPages;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Resources\Pages\EditRecord;
use Livewire\Component;
use Livewire\ComponentHookRegistry;

function feature(bool|array $value): void
{
    config()->set('filament-tweaks.features.hide_relation_managers_on_edit_pages', $value);
}

function currentPanel(string $id = 'admin'): Panel
{
    $panel = Panel::make()->id($id)->path($id);

    Filament::setCurrentPanel($panel);

    return $panel;
}

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

function plainEditPage(): EditRecord
{
    return new class extends EditRecord {};
}

function registeredComponentHooks(): array
{
    return array_map(
        fn ($hook) => is_object($hook) ? $hook::class : $hook,
        (new ReflectionProperty(ComponentHookRegistry::class, 'componentHooks'))->getValue(),
    );
}

it('is disabled by default', function () {
    expect(config('filament-tweaks.features.hide_relation_managers_on_edit_pages'))->toBeFalse();
});

it('registers the hook when the feature is enabled', function (bool|array $value) {
    feature($value);

    (new FilamentTweaksServiceProvider(app()))->packageBooted();

    expect(registeredComponentHooks())->toContain(HidesRelationManagersOnEditPages::class);
})->with([
    'every panel' => true,
    'a single panel' => [['admin']],
]);

it('empties the relation managers of a plain edit page', function () {
    feature(true);
    currentPanel();

    expect(cachedRelationManagers(bootHookOn(plainEditPage())))->toBe([]);
});

it('does nothing while the feature is disabled', function (bool|array $value) {
    feature($value);
    currentPanel();

    expect(cachedRelationManagers(bootHookOn(plainEditPage())))->toBeNull();
})->with([
    'disabled' => false,
    'an empty panel list' => [[]],
]);

it('only applies to the listed panels', function () {
    feature(['admin']);

    currentPanel('admin');
    expect(cachedRelationManagers(bootHookOn(plainEditPage())))->toBe([]);

    currentPanel('app');
    expect(cachedRelationManagers(bootHookOn(plainEditPage())))->toBeNull();
});

it('leaves a page that declares its own relation managers alone', function () {
    feature(true);
    currentPanel();

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
    feature(true);
    currentPanel();

    $page = bootHookOn(new class extends EditRecord implements ShowsRelationManagers {});

    expect(cachedRelationManagers($page))->toBeNull();
});

it('ignores components that are not edit pages', function () {
    feature(true);

    $hook = new HidesRelationManagersOnEditPages;
    $hook->setComponent(new class extends Component
    {
        public function render()
        {
            return '<div></div>';
        }
    });

    $hook->boot();
})->throwsNoExceptions();
