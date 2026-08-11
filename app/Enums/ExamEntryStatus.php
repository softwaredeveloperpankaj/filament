<?php
namespace App\Enums;

enum ExamEntryStatus: string {
    case ENROLLED = 'enrolled';
    case ADMIT_CARD_GENERATED = 'admit_card_generated';
    case APPEARED = 'appeared';
    case ABSENT = 'absent';
    case DEBARRED = 'debarred';
    case WITHDRAWN = 'withdrawn';
}