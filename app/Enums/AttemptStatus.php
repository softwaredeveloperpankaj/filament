<?php
namespace App\Enums;

enum AttemptStatus: string {
    case NOT_STARTED = 'not_started';
    case IN_PROGRESS = 'in_progress';
    case SUBMITTED = 'submitted';
    case AUTO_SUBMITTED = 'auto_submitted';
    case TERMINATED = 'terminated';
}