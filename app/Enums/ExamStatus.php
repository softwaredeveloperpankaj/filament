<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ExamStatus: string implements HasLabel, HasColor
{
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case ONGOING = 'ongoing';
    case COMPLETED = 'completed';
    case RESULTS_PUBLISHED = 'results_published';
    case CANCELLED = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT             => 'Draft',
            self::SCHEDULED         => 'Scheduled',
            self::ONGOING           => 'Ongoing',
            self::COMPLETED         => 'Completed',
            self::RESULTS_PUBLISHED => 'Results Published',
            self::CANCELLED         => 'Cancelled',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DRAFT             => 'gray',
            self::SCHEDULED         => 'info',
            self::ONGOING           => 'warning',
            self::COMPLETED         => 'primary',
            self::RESULTS_PUBLISHED => 'success',
            self::CANCELLED         => 'danger',
        };
    }

    public static function forFilamentSelect(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])
            ->toArray();
    }
}