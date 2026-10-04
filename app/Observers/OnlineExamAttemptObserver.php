<?php

namespace App\Observers;

use App\Enums\AttemptStatus;
use App\Models\OnlineExamAttempt;
use Carbon\Carbon;

class OnlineExamAttemptObserver
{
    /**
     * Handle the OnlineExamAttempt "saving" event.
     */
    public function saving(OnlineExamAttempt $attempt): void
    {
        if ($attempt->started_at && empty($attempt->ended_at) && $attempt->examSubject?->duration_minutes) {
            $attempt->ended_at = Carbon::parse($attempt->started_at)->addMinutes($attempt->examSubject->duration_minutes);
        }

        // Auto submit if expired and still in progress
        if ($attempt->status === AttemptStatus::IN_PROGRESS && $attempt->ended_at && Carbon::now()->isAfter($attempt->ended_at)) {
            $attempt->status = AttemptStatus::AUTO_SUBMITTED;
            $attempt->submitted_at = $attempt->ended_at;
        }
    }
}