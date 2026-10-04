<?php

namespace App\Listeners;

use App\Events\ExamScheduled;
use App\Notifications\ExamStartingSoon;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendExamReminder implements ShouldQueue
{
    public function handle(ExamScheduled $event): void
    {
        // Scheduled job or instant reminder
    }
}