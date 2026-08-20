<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ExamMode: string implements HasLabel, HasColor
{
    case ONLINE = 'online';
    case OFFLINE = 'offline';

    public function getLabel(): string
    {
        return match ($this) {
            self::ONLINE  => 'Online',
            self::OFFLINE => 'Offline',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ONLINE  => 'success',
            self::OFFLINE => 'info',
        };
    }

    public static function forFilamentSelect(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])
            ->toArray();
    }
}