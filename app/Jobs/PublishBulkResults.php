<?php

namespace App\Jobs;

use App\Models\Exam;
use App\Services\ResultGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishBulkResults implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $examId, public ?int $publishedBy = null) {}

    public function handle(ResultGenerationService $service): void
    {
        $exam = Exam::findOrFail($this->examId);
        $service->publishResults($exam, $this->publishedBy);
    }
}