<?php

namespace Dowhile\FilamentTweaks\Filters;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class TextFilter extends BaseFilter
{
    public string $name;

    protected string $operator = 'like';

    protected function setUp(): void
    {
        parent::setUp();

        $this->form([
            TextInput::make($this->name)
                ->label(fn (): string => $this->getLabel()),
        ])
            ->query(function (Builder $query, array $state): Builder {
                return ! empty($state[$this->name])
                    ? $query->where($this->name, $this->operator, "%{$state[$this->name]}%")
                    : $query;
            })
            ->indicateUsing(function (array $state): ?Indicator {
                return ! empty($state[$this->name])
                    ? Indicator::make($this->getLabel().': '.$state[$this->name])
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

    public function numeric(bool $numeric = true): self
    {
        $this->getFormSchema()[0]->numeric($numeric);

        return $this;
    }
}
