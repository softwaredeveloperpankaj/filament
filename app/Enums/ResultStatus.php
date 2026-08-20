<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ResultStatus: string implements HasLabel, HasColor
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case WITHHELD = 'withheld';
    case CANCELLED = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT     => 'Draft',
            self::PUBLISHED => 'Published',
            self::WITHHELD  => 'Withheld',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DRAFT     => 'gray',
            self::PUBLISHED => 'success',
            self::WITHHELD  => 'warning',
            self::CANCELLED => 'danger',
        };
    }

    public static function forFilamentSelect(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])
            ->toArray();
    }
}