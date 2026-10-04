<?php

namespace App\Jobs;

use App\Enums\AttemptStatus;
use App\Models\OnlineExamAttempt;
use App\Services\OnlineExamSubmissionService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AutoSubmitExpiredAttempts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(OnlineExamSubmissionService $service): void
    {
        $expired = OnlineExamAttempt::where('status', AttemptStatus::IN_PROGRESS)
            ->whereNotNull('ended_at')
            ->where('ended_at', '<', Carbon::now())
            ->get();

        foreach ($expired as $attempt) {
            $answers = $attempt->answers ?? [];
            $service->submitAttempt($attempt, $answers, true);
        }
    }
}