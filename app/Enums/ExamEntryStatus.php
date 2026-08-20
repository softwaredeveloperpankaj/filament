<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ExamEntryStatus: string implements HasLabel, HasColor
{
    case ENROLLED = 'enrolled';
    case ADMIT_CARD_GENERATED = 'admit_card_generated';
    case APPEARED = 'appeared';
    case ABSENT = 'absent';
    case DEBARRED = 'debarred';
    case WITHDRAWN = 'withdrawn';

    public function getLabel(): string
    {
        return match ($this) {
            self::ENROLLED             => 'Enrolled',
            self::ADMIT_CARD_GENERATED => 'Admit Card Generated',
            self::APPEARED             => 'Appeared',
            self::ABSENT               => 'Absent',
            self::DEBARRED             => 'Debarred',
            self::WITHDRAWN            => 'Withdrawn',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ENROLLED             => 'info',
            self::ADMIT_CARD_GENERATED => 'primary',
            self::APPEARED             => 'success',
            self::ABSENT               => 'warning',
            self::DEBARRED             => 'danger',
            self::WITHDRAWN            => 'gray',
        };
    }

    public static function forFilamentSelect(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])
            ->toArray();
    }
}