<?php
namespace App\Enums;

enum QuestionType: string {
    case MCQ = 'mcq';
    case TRUE_FALSE = 'true_false';
    case FILL_BLANK = 'fill_blank';
    case SHORT_ANSWER = 'short_answer';
    case LONG_ANSWER = 'long_answer';
    case MATCHING = 'matching';
}