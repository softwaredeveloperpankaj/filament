<?php

namespace App\Filament\Pages;

use App\Models\Exam;
use App\Models\ExamResult;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ExamAnalytics extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;
    protected static string|\UnitEnum|null $navigationGroup = 'Examinations';
    protected static ?int $navigationSort = 14;
    protected static ?string $title = 'Exam Analytics';

    protected string $view = 'filament.pages.exam-analytics';

    public ?int $exam_id = null;
    public array $analytics = [];

    public function mount(): void
    {
        $firstExam = Exam::orderByDesc('id')->first();
        if ($firstExam) {
            $this->exam_id = $firstExam->id;
            $this->loadAnalytics();
        }
    }

    public function updatedExamId(): void
    {
        $this->loadAnalytics();
    }

    public function loadAnalytics(): void
    {
        if (!$this->exam_id) {
            $this->analytics = [];
            return;
        }

        $results = ExamResult::with('student')
            ->where('exam_id', $this->exam_id)
            ->get();

        $total = $results->count();
        $passed = $results->where('is_passed', true)->count();
        $grades = $results->groupBy('overall_grade')->map->count()->toArray();
        $topPerformers = $results->sortByDesc('grand_total_obtained')->take(5)->map(fn($r) => [
            'name' => $r->student?->name ?? 'Student #' . $r->student_id,
            'percentage' => $r->overall_percentage,
            'grade' => $r->overall_grade,
            'rank' => $r->rank_in_class,
        ])->values()->toArray();

        $this->analytics = [
            'total_students' => $total,
            'passed' => $passed,
            'failed' => $total - $passed,
            'pass_rate' => $total > 0 ? round(($passed / $total) * 100, 1) : 0,
            'grade_distribution' => $grades,
            'top_performers' => $topPerformers,
        ];
    }
}