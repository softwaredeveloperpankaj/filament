<?php

namespace App\Filament\Pages;

use App\Models\Exam;
use App\Models\ExamQuestionPaper;
use App\Models\ExamSubject;
use App\Services\QuestionPaperService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class PrintExamPapers extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPrinter;
    protected static string|\UnitEnum|null $navigationGroup = 'Examinations';
    protected static ?int $navigationSort = 12;
    protected static ?string $title = 'Print Question Papers';

    protected string $view = 'filament.pages.print-exam-papers';

    public ?int $exam_id = null;
    public ?int $exam_subject_id = null;
    public ?array $paperDetails = null;

    public function updatedExamId(): void
    {
        $this->exam_subject_id = null;
        $this->paperDetails = null;
    }

    public function updatedExamSubjectId(): void
    {
        $this->loadPaper();
    }

    public function loadPaper(): void
    {
        if (!$this->exam_id || !$this->exam_subject_id) {
            $this->paperDetails = null;
            return;
        }

        $paper = ExamQuestionPaper::where('exam_id', $this->exam_id)
            ->where('exam_subject_id', $this->exam_subject_id)
            ->first();

        if (!$paper) {
            $this->paperDetails = null;
            return;
        }

        $this->paperDetails = [
            'id' => $paper->id,
            'total_marks' => $paper->total_marks,
            'questions_count' => is_array($paper->selected_questions) ? count($paper->selected_questions) : 0,
            'instructions' => $paper->instructions,
        ];
    }

    public function downloadPdf()
    {
        if (!$this->exam_id || !$this->exam_subject_id) {
            return null;
        }

        $paper = ExamQuestionPaper::where('exam_id', $this->exam_id)
            ->where('exam_subject_id', $this->exam_subject_id)
            ->first();

        if (!$paper) {
            Notification::make()->title('Paper not generated yet')->warning()->send();
            return null;
        }

        $pdf = QuestionPaperService::generatePdf($paper);

        return response()->streamDownload(
            fn () => print($pdf),
            "question-paper-{$paper->id}.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }
}