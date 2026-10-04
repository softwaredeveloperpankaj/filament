<?php

namespace App\Services;

use App\Enums\ExamStatus;
use App\Enums\ResultStatus;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\ExamResult;
use App\Models\ExamStudentEntry;
use App\Models\ExamSubject;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ResultGenerationService
{
    protected MarksCalculationService $marksService;
    protected RankCalculationService $rankService;

    public function __construct()
    {
        $this->marksService = new MarksCalculationService();
        $this->rankService = new RankCalculationService();
    }

    public function determinePersistedStatus(?ExamResult $existingResult): ResultStatus
    {
        if (!$existingResult) {
            return ResultStatus::DRAFT;
        }

        $status = $existingResult->status;

        if ($status instanceof ResultStatus) {
            return $status;
        }

        if (is_string($status)) {
            return ResultStatus::tryFrom($status) ?? ResultStatus::DRAFT;
        }

        return ResultStatus::DRAFT;
    }

    /**
     * Generate or update ExamResult for a single student entry in an exam.
     */
    public function generateResultForStudent(Exam $exam, ExamStudentEntry $entry): ExamResult
    {
        $existingResult = ExamResult::where('exam_id', $exam->id)
            ->where('student_id', $entry->student_id)
            ->first();

        $examSubjects = ExamSubject::with('subject')
            ->where('exam_id', $exam->id)
            ->orderBy('display_order')
            ->get();

        $marks = ExamMark::where('exam_id', $exam->id)
            ->where('exam_student_entry_id', $entry->id)
            ->get()
            ->keyBy('exam_subject_id');

        $grandTotalObtained = 0;
        $grandTotalMaximum = 0;
        $failedSubjects = [];
        $breakdown = [];
        $allPassed = true;

        foreach ($examSubjects as $examSub) {
            $mark = $marks->get($examSub->id);

            $obtained = (int) ($mark?->total_obtained ?? 0);
            $maximum = (int) ($mark?->total_maximum ?? ($examSub->theory_marks + $examSub->practical_marks) ?: $examSub->max_marks ?: 100);
            $passMarks = (int) ($examSub->pass_marks ?? 33);
            $isSubjectPassed = $mark?->absent || $mark?->exempted ? false : ($obtained >= $passMarks);

            if ($examSub->is_graded) {
                $grandTotalObtained += $obtained;
                $grandTotalMaximum += $maximum;

                if (!$isSubjectPassed) {
                    $allPassed = false;
                    $failedSubjects[] = $examSub->subject_id;
                }
            }

            $percentage = ($maximum > 0) ? round(($obtained / $maximum) * 100, 2) : 0;
            $grade = $mark?->grade ?? $this->marksService->calculateGrade($percentage);

            $breakdown[] = [
                'subject_id' => $examSub->subject_id,
                'subject_name' => $examSub->subject?->name ?? 'Subject #' . $examSub->subject_id,
                'subject_code' => $examSub->subject?->code ?? null,
                'theory_obtained' => $mark?->theory_obtained ?? 0,
                'practical_obtained' => $mark?->practical_obtained ?? 0,
                'internal_obtained' => $mark?->internal_obtained ?? 0,
                'total_obtained' => $obtained,
                'total_maximum' => $maximum,
                'pass_marks' => $passMarks,
                'is_passed' => $isSubjectPassed,
                'grade' => $grade,
                'is_graded' => (bool) $examSub->is_graded,
                'absent' => (bool) ($mark?->absent ?? false),
                'exempted' => (bool) ($mark?->exempted ?? false),
                'remarks' => $mark?->remarks,
            ];
        }

        $overallPercentage = ($grandTotalMaximum > 0) ? round(($grandTotalObtained / $grandTotalMaximum) * 100, 2) : 0;
        $overallGrade = $this->marksService->calculateGrade($overallPercentage);

        $result = ExamResult::updateOrCreate(
            [
                'exam_id' => $exam->id,
                'student_id' => $entry->student_id,
            ],
            [
                'exam_student_entry_id' => $entry->id,
                'grand_total_obtained' => $grandTotalObtained,
                'grand_total_maximum' => $grandTotalMaximum,
                'overall_percentage' => $overallPercentage,
                'overall_grade' => $overallGrade,
                'is_passed' => $allPassed,
                'failed_subjects' => $failedSubjects,
                'subject_wise_breakdown' => $breakdown,
                'status' => $this->determinePersistedStatus($existingResult),
            ]
        );

        event(new \App\Events\ResultCalculated($result));

        return $result;
    }

    /**
     * Generate results for all enrolled students in an exam, then compute ranks.
     */
    public function generateResultsForExam(Exam $exam): int
    {
        $entries = ExamStudentEntry::where('exam_id', $exam->id)->get();
        $generatedCount = 0;

        DB::transaction(function () use ($exam, $entries, &$generatedCount) {
            foreach ($entries as $entry) {
                $this->generateResultForStudent($exam, $entry);
                $generatedCount++;
            }

            // Calculate section, class, and branch ranks
            $this->rankService->calculateRanksForExam($exam);
        });

        return $generatedCount;
    }

    /**
     * Publish results for an exam.
     */
    public function publishResults(Exam $exam, ?int $publishedBy = null): int
    {
        $results = ExamResult::where('exam_id', $exam->id)
            ->whereIn('status', [ResultStatus::DRAFT, ResultStatus::WITHHELD])
            ->get();

        $count = 0;

        foreach ($results as $result) {
            if (!$result->canTransitionTo(ResultStatus::PUBLISHED)) {
                continue;
            }

            $result->update([
                'status' => ResultStatus::PUBLISHED,
                'published_by' => $publishedBy,
                'published_at' => Carbon::now(),
            ]);

            $count++;
        }

        if ($count > 0) {
            $exam->update(['status' => ExamStatus::RESULTS_PUBLISHED]);
        }

        return $count;
    }
}