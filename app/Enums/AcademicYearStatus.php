<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AcademicYearStatus: string implements HasLabel, HasColor
{
    case UPCOMING  = 'upcoming';
    case ACTIVE    = 'active';
    case COMPLETED = 'completed';
    case ARCHIVED  = 'archived';

    public function getLabel(): string
    {
        return match ($this) {
            self::UPCOMING  => 'Upcoming',
            self::ACTIVE    => 'Active',
            self::COMPLETED => 'Completed',
            self::ARCHIVED  => 'Archived',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::UPCOMING  => 'info',
            self::ACTIVE    => 'success',
            self::COMPLETED => 'warning',
            self::ARCHIVED  => 'gray',
        };
    }

    public static function forFilamentSelect(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($c) => [$c->value => $c->getLabel()])
            ->toArray();
    }
}