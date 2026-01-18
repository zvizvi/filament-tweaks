<?php

namespace Dowhile\FilamentTweaks\Filters;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class RangeFilter extends Filter
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->schema([
            Fieldset::make(fn () => $this->getLabel())
                ->schema([
                    TextInput::make($this->getFromFieldName())
                        ->label('מינימום')
                        ->minValue(0)
                        ->numeric(),
                    TextInput::make($this->getToFieldName())
                        ->label('מקסימום')
                        ->minValue(0)
                        ->numeric(),
                ])
                ->columnSpanFull(),
        ]);

        $this->query(function (Builder $query, array $data): Builder {
            return $query
                ->when(
                    $data[$this->getFromFieldName()],
                    fn (Builder $query, $value): Builder => $query->where($this->getName(), '>=', $value),
                )
                ->when(
                    $data[$this->getToFieldName()],
                    fn (Builder $query, $value): Builder => $query->where($this->getName(), '<=', $value),
                );
        });

        $this->indicateUsing(function (array $data): ?Indicator {
            return ! empty($data[$this->getFromFieldName()]) || ! empty($data[$this->getToFieldName()])
                ? Indicator::make($this->getLabel().': '.collect([
                    $data[$this->getFromFieldName()] ? "מ-{$data[$this->getFromFieldName()]}" : null,
                    $data[$this->getToFieldName()] ? "עד-{$data[$this->getToFieldName()]}" : null,
                ])
                    ->filter()
                    ->implode(' '))
                : null;
        });

        $this->columns(2);
    }

    public function getFromFieldName(): string
    {
        return $this->getName().'_from';
    }

    public function getToFieldName(): string
    {
        return $this->getName().'_to';
    }
}
