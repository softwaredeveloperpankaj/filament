<?php

namespace App\Enums;

enum AcademicTermType: string
{
    case ANNUAL      = 'annual';
    case SEMESTER    = 'semester';    // 2 terms
    case TRIMESTER   = 'trimester';   // 3 terms
    case QUARTER     = 'quarter';     // 4 terms
    case MONTHLY     = 'monthly';     // 12 terms

    public function label(): string
    {
        return match ($this) {
            self::ANNUAL    => 'Annual',
            self::SEMESTER  => 'Semester (2 Terms)',
            self::TRIMESTER => 'Trimester (3 Terms)',
            self::QUARTER   => 'Quarter (4 Terms)',
            self::MONTHLY   => 'Monthly (12 Terms)',
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
}