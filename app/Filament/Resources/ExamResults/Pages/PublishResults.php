<?php

namespace App\Filament\Resources\ExamResults\Pages;

use App\Enums\ResultStatus;
use App\Filament\Resources\ExamResults\ExamResultResource;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Services\ResultGenerationService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class PublishResults extends Page
{
    protected static string $resource = ExamResultResource::class;

    protected string $view = 'filament.resources.exam-results.pages.publish-results';

    protected static ?string $title = 'Review & Publish Results';

    public ?int $exam_id = null;
    public array $resultsList = [];
    public array $summary = [];

    public function mount(): void
    {
        $this->exam_id = request()->query('exam_id') ? (int) request()->query('exam_id') : null;
        if ($this->exam_id) {
            $this->loadResults();
        }
    }

    public function updatedExamId(): void
    {
        $this->loadResults();
    }

    public function loadResults(): void
    {
        if (!$this->exam_id) {
            $this->resultsList = [];
            $this->summary = [];
            return;
        }

        $results = ExamResult::with(['student', 'entry'])
            ->where('exam_id', $this->exam_id)
            ->orderBy('rank_in_class')
            ->get();

        $this->resultsList = [];
        $passed = 0;
        $failed = 0;
        $totalMarksSum = 0;
        $totalMaxSum = 0;

        foreach ($results as $r) {
            if ($r->is_passed) {
                $passed++;
            } else {
                $failed++;
            }
            $totalMarksSum += $r->grand_total_obtained;
            $totalMaxSum += $r->grand_total_maximum;

            $this->resultsList[] = [
                'id' => $r->id,
                'student_name' => $r->student?->name ?? 'Student #' . $r->student_id,
                'roll_no' => $r->entry?->roll_no ?? '—',
                'obtained' => $r->grand_total_obtained,
                'maximum' => $r->grand_total_maximum,
                'percentage' => $r->overall_percentage,
                'grade' => $r->overall_grade,
                'rank_class' => $r->rank_in_class,
                'rank_section' => $r->rank_in_section,
                'is_passed' => $r->is_passed,
                'status' => $r->status instanceof ResultStatus ? $r->status->value : (string) $r->status,
            ];
        }

        $totalCount = count($results);
        $this->summary = [
            'total' => $totalCount,
            'passed' => $passed,
            'failed' => $failed,
            'pass_percentage' => $totalCount > 0 ? round(($passed / $totalCount) * 100, 1) : 0,
            'avg_percentage' => $totalMaxSum > 0 ? round(($totalMarksSum / $totalMaxSum) * 100, 1) : 0,
        ];
    }

    public function calculateResults(): void
    {
        if (!$this->exam_id) {
            return;
        }

        $exam = Exam::findOrFail($this->exam_id);
        $service = new ResultGenerationService();
        $count = $service->generateResultsForExam($exam);

        $this->loadResults();

        Notification::make()
            ->title('Results Calculated')
            ->body("Compiled and ranked results for {$count} students.")
            ->success()
            ->send();
    }

    public function publishAll(): void
    {
        if (!$this->exam_id) {
            return;
        }

        $exam = Exam::findOrFail($this->exam_id);
        $service = new ResultGenerationService();
        $count = $service->publishResults($exam, Auth::id());

        $this->loadResults();

        Notification::make()
            ->title('Results Published')
            ->body("Published results for {$count} students.")
            ->success()
            ->send();
    }
}