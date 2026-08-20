<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MarksSource: string implements HasLabel, HasColor
{
    case ONLINE_AUTO = 'online_auto';
    case ONLINE_MANUAL = 'online_manual';
    case OFFLINE_MANUAL = 'offline_manual';

    public function getLabel(): string
    {
        return match ($this) {
            self::ONLINE_AUTO    => 'Online Auto',
            self::ONLINE_MANUAL  => 'Online Manual',
            self::OFFLINE_MANUAL => 'Offline Manual',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ONLINE_AUTO    => 'info',
            self::ONLINE_MANUAL  => 'warning',
            self::OFFLINE_MANUAL => 'gray',
        };
    }

    public static function forFilamentSelect(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])
            ->toArray();
    }
}