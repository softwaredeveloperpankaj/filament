<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AcademicTermType: string implements HasLabel, HasColor
{
    case ANNUAL      = 'annual';
    case SEMESTER    = 'semester';    // 2 terms
    case TRIMESTER   = 'trimester';   // 3 terms
    case QUARTER     = 'quarter';     // 4 terms
    case MONTHLY     = 'monthly';     // 12 terms

    public function getLabel(): string
    {
        return match ($this) {
            self::ANNUAL    => 'Annual',
            self::SEMESTER  => 'Semester (2 Terms)',
            self::TRIMESTER => 'Trimester (3 Terms)',
            self::QUARTER   => 'Quarter (4 Terms)',
            self::MONTHLY   => 'Monthly (12 Terms)',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ANNUAL    => 'indigo',
            self::SEMESTER  => 'indigo',
            self::TRIMESTER => 'indigo',
            self::QUARTER   => 'indigo',
            self::MONTHLY   => 'indigo',
        };
    }     

    public function count(): int
    {
        return match ($this) {
            self::ANNUAL    => 1,
            self::SEMESTER  => 2,
            self::TRIMESTER => 3,
            self::QUARTER   => 4,
            self::MONTHLY   => 12,
        };
    }

    public static function forFilamentSelect(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($c) => [$c->value => $c->getLabel()])
            ->toArray();
    }
}