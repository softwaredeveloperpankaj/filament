<?php
namespace App\Enums;

enum QuestionType: string {
    case MCQ = 'mcq';
    case TRUE_FALSE = 'true_false';
    case FILL_BLANK = 'fill_blank';
    case SHORT_ANSWER = 'short_answer';
    case LONG_ANSWER = 'long_answer';
    case MATCHING = 'matching';

    public function label(): string
    {
        return match ($this) {
            self::MCQ => 'Multiple Choice',
            self::TRUE_FALSE => 'True / False',
            self::FILL_BLANK => 'Fill in the Blank',
            self::SHORT_ANSWER => 'Short Answer',
            self::LONG_ANSWER => 'Long Answer',
            self::MATCHING => 'Matching',
        };
    }

    public static function forFilamentSelect(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($c) => [$c->value => $c->label()])
            ->toArray();
    }    
}