<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum QuestionType: string implements HasLabel, HasColor
{
    case MCQ = 'mcq';
    case TRUE_FALSE = 'true_false';
    case FILL_BLANK = 'fill_blank';
    case SHORT_ANSWER = 'short_answer';
    case LONG_ANSWER = 'long_answer';
    case MATCHING = 'matching';

    public function getLabel(): string
    {
        return match ($this) {
            self::MCQ          => 'Multiple Choice',
            self::TRUE_FALSE   => 'True / False',
            self::FILL_BLANK   => 'Fill in the Blank',
            self::SHORT_ANSWER => 'Short Answer',
            self::LONG_ANSWER  => 'Long Answer',
            self::MATCHING     => 'Matching',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::MCQ          => 'info',
            self::TRUE_FALSE   => 'success',
            self::FILL_BLANK   => 'warning',
            self::SHORT_ANSWER => 'primary',
            self::LONG_ANSWER  => 'gray',
            self::MATCHING     => 'info',
        };
    }

    public static function forFilamentSelect(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])
            ->toArray();
    }
}