<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AttemptStatus: string implements HasLabel, HasColor
{
    case NOT_STARTED = 'not_started';
    case IN_PROGRESS = 'in_progress';
    case SUBMITTED = 'submitted';
    case AUTO_SUBMITTED = 'auto_submitted';
    case TERMINATED = 'terminated';

    public function getLabel(): string
    {
        return match ($this) {
            self::NOT_STARTED  => 'Not Started',
            self::IN_PROGRESS    => 'In Progress',
            self::SUBMITTED => 'Submitted',
            self::AUTO_SUBMITTED  => 'Auto Submitted',
            self::TERMINATED  => 'Terminated',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::NOT_STARTED  => 'info',
            self::IN_PROGRESS    => 'success',
            self::SUBMITTED => 'warning',
            self::AUTO_SUBMITTED  => 'gray',
            self::TERMINATED  => 'danger',
        };
    }

    public static function forFilamentSelect(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($c) => [$c->value => $c->getLabel()])
            ->toArray();
    }    
}