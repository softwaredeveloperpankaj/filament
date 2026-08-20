<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum GradeScale: string implements HasLabel, HasColor
{
    case A_PLUS  = 'A+';
    case A       = 'A';
    case B_PLUS  = 'B+';
    case B       = 'B';
    case C_PLUS  = 'C+';
    case C       = 'C';
    case D       = 'D';
    case F       = 'F';

    // ─── Filament HasLabel Contract ───
    public function getLabel(): string
    {
        return $this->value;
    }

    // ─── Filament HasColor Contract ───
    public function getColor(): string
    {
        return match ($this) {
            self::A_PLUS, self::A => 'success',
            self::B_PLUS, self::B => 'info',
            self::C_PLUS, self::C => 'warning',
            self::D               => 'gray',
            self::F               => 'danger',
        };
    }

    // ─── Filament Select Options ───
    public static function forFilamentSelect(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($grade) => [$grade->value => $grade->getLabel()])
            ->toArray();
    }

    // ─── Grade Point (for GPA) ───
    public function gradePoint(): float
    {
        return match ($this) {
            self::A_PLUS => 4.0,
            self::A      => 4.0,
            self::B_PLUS => 3.5,
            self::B      => 3.0,
            self::C_PLUS => 2.5,
            self::C      => 2.0,
            self::D      => 1.0,
            self::F      => 0.0,
        };
    }

    // ─── Minimum Percentage (customize per school) ───
    public function minPercentage(): int
    {
        return match ($this) {
            self::A_PLUS => 90,
            self::A      => 80,
            self::B_PLUS => 70,
            self::B      => 60,
            self::C_PLUS => 50,
            self::C      => 40,
            self::D      => 33,
            self::F      => 0,
        };
    }

    // ─── Maximum Percentage ───
    public function maxPercentage(): int
    {
        return match ($this) {
            self::A_PLUS => 100,
            self::A      => 89,
            self::B_PLUS => 79,
            self::B      => 69,
            self::C_PLUS => 59,
            self::C      => 49,
            self::D      => 42,
            self::F      => 32,
        };
    }

    // ─── Determine Grade from Percentage ───
    public static function fromPercentage(float $percentage): self
    {
        return collect(self::cases())
            ->filter(fn ($g) => $percentage >= $g->minPercentage() && $percentage <= $g->maxPercentage())
            ->first() ?? self::F;
    }
}