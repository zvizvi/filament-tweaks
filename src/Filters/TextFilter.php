<?php

namespace Dowhile\FilamentTweaks\Filters;

use Filament\Forms\Components\Field;
use Filament\Tables\Filters\BaseFilter;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Indicator;

class TextFilter extends BaseFilter
{
    public string $name;
    protected string $operator = 'like';

    protected function setUp(): void
    {
        parent::setUp();

        $this->form([
            TextInput::make($this->name)
                ->label(fn() => $this->getLabel()),
        ])
            ->query(function (Builder $query, array $state) {
                return !empty($state[$this->name])
                    ? $query->where($this->name, $this->operator, "%{$state[$this->name]}%")
                    : $query;
            })
            ->indicateUsing(function (array $state) {
                return !empty($state[$this->name])
                    ? Indicator::make($this->getLabel() . ': ' . $state[$this->name])
                    : null;
            });
    }

    public static function make(?string $name = null, string $operator = 'like'): static
    {
        $instance = parent::make($name);
        $instance->operator = $operator;
        return $instance;
    }

    public function operator(string $operator): self
    {
        $this->operator = $operator;
        return $this;
    }
}
