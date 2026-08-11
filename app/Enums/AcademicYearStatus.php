<?php

namespace App\Enums;

enum AcademicYearStatus: string
{
    case UPCOMING  = 'upcoming';
    case ACTIVE    = 'active';
    case COMPLETED = 'completed';
    case ARCHIVED  = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::UPCOMING  => 'Upcoming',
            self::ACTIVE    => 'Active',
            self::COMPLETED => 'Completed',
            self::ARCHIVED  => 'Archived',
        };
    }

    public function color(): string
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
            ->mapWithKeys(fn($c) => [$c->value => $c->label()])
            ->toArray();
    }
}