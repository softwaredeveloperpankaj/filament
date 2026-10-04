<?php

namespace App\Jobs;

use App\Models\Exam;
use App\Models\ExamSubject;
use App\Services\ExamPaperBuilderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateBulkQuestionPapers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $examId) {}

    public function handle(ExamPaperBuilderService $service): void
    {
        $exam = Exam::findOrFail($this->examId);
        $subjects = ExamSubject::where('exam_id', $exam->id)->get();

        foreach ($subjects as $subject) {
            $service->buildPaper($exam, $subject, ['auto_generate' => true]);
        }
    }
}