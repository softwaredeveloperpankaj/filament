<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RoomNumber: string implements HasLabel, HasColor
{
    case A_101 = 'A-101';
    case A_102 = 'A-102';
    case A_103 = 'A-103';
    case A_104 = 'A-104';
    case A_105 = 'A-105';
    case B_101 = 'B-101';
    case B_102 = 'B-102';
    case B_103 = 'B-103';
    case B_104 = 'B-104';
    case B_105 = 'B-105';
    case HALL_A = 'Hall A';
    case HALL_B = 'Hall B';
    case HALL_C = 'Hall C';
    case LAB_1 = 'Lab 1';
    case LAB_2 = 'Lab 2';
    case LAB_3 = 'Lab 3';
    case LAB_4 = 'Lab 4';
    case LAB_5 = 'Lab 5';
    case CUSTOM = 'Custom';

    public function getLabel(): string
    {
        return match ($this) {
            self::A_101 => 'A-101',
            self::A_102 => 'A-102',
            self::A_103 => 'A-103',
            self::A_104 => 'A-104',
            self::A_105 => 'A-105',
            self::B_101 => 'B-101',
            self::B_102 => 'B-102',
            self::B_103 => 'B-103',
            self::B_104 => 'B-104',
            self::B_105 => 'B-105',
            self::HALL_A => 'Hall A',
            self::HALL_B => 'Hall B',
            self::HALL_C => 'Hall C',
            self::LAB_1 => 'Lab 1',
            self::LAB_2 => 'Lab 2',
            self::LAB_3 => 'Lab 3',
            self::LAB_4 => 'Lab 4',
            self::LAB_5 => 'Lab 5',
            self::CUSTOM => 'Custom (Enter manually)',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::A_101, self::A_102, self::A_103, self::A_104, self::A_105 => 'blue',
            self::B_101, self::B_102, self::B_103, self::B_104, self::B_105 => 'green',
            self::HALL_A, self::HALL_B, self::HALL_C => 'amber',
            self::LAB_1, self::LAB_2, self::LAB_3, self::LAB_4, self::LAB_5 => 'purple',
            self::CUSTOM => 'gray',
        };
    }

    public static function forFilamentSelect(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])
            ->toArray();
    }

    public static function getPredefinedRooms(): array
    {
        return collect(self::cases())
            ->filter(fn ($c) => $c !== self::CUSTOM)
            ->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])
            ->toArray();
    }
}