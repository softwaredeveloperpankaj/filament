<?php

namespace App\Filament\Pages;

use App\Enums\AttemptStatus;
use App\Models\Exam;
use App\Models\OnlineExamAttempt;
use App\Services\OnlineExamSubmissionService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class StudentExamPortal extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedComputerDesktop;
    protected static string|\UnitEnum|null $navigationGroup = 'Examinations';
    protected static ?int $navigationSort = 13;
    protected static ?string $title = 'Online Exam Portal';

    protected string $view = 'filament.pages.student-exam-portal';

    public ?int $selected_attempt_id = null;
    public array $questions = [];
    public array $answers = [];
    public ?string $status = null;
    public ?int $score = null;

    public function loadAttempt(int $attemptId): void
    {
        $attempt = OnlineExamAttempt::with(['examSubject.subject', 'exam'])->findOrFail($attemptId);
        $this->selected_attempt_id = $attempt->id;
        $this->status = $attempt->status instanceof AttemptStatus ? $attempt->status->value : (string) $attempt->status;
        $this->score = $attempt->total_obtained;

        $paper = $attempt->examSubject?->questionPaper;
        $this->questions = [];
        $this->answers = $attempt->answers ?? [];

        if ($paper && $paper->questions) {
            foreach ($paper->questions as $q) {
                $this->questions[] = [
                    'id' => $q->id,
                    'text' => $q->question_text,
                    'type' => $q->question_type?->value ?? 'mcq',
                    'options' => $q->options ?? [],
                    'marks' => $q->marks,
                ];
            }
        }
    }

    public function startExam(): void
    {
        if (!$this->selected_attempt_id) return;
        $attempt = OnlineExamAttempt::findOrFail($this->selected_attempt_id);
        $attempt->update([
            'status' => AttemptStatus::IN_PROGRESS,
            'started_at' => Carbon::now(),
        ]);
        $this->loadAttempt($this->selected_attempt_id);
    }

    public function submitExam(): void
    {
        if (!$this->selected_attempt_id) return;
        $attempt = OnlineExamAttempt::findOrFail($this->selected_attempt_id);
        $service = new OnlineExamSubmissionService();
        $service->submitAttempt($attempt, $this->answers);

        Notification::make()->title('Exam submitted successfully!')->success()->send();
        $this->loadAttempt($this->selected_attempt_id);
    }
}