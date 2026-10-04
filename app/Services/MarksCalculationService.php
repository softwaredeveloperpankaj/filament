<?php

namespace App\Services;

use App\Enums\GradeScale;
use App\Models\ExamMark;
use App\Models\ExamSubject;
use Illuminate\Support\Facades\DB;

class MarksCalculationService
{
    /**
     * Compute and update calculated fields on an ExamMark record.
     */
    public function calculateMark(ExamMark $mark): ExamMark
    {
        $theoryObtained = (int) ($mark->theory_obtained ?? 0);
        $theoryMax = (int) ($mark->theory_maximum ?? 0);

        $practicalObtained = (int) ($mark->practical_obtained ?? 0);
        $practicalMax = (int) ($mark->practical_maximum ?? 0);

        $internalObtained = (int) ($mark->internal_obtained ?? 0);
        $internalMax = (int) ($mark->internal_maximum ?? 0);

        if ($mark->absent || $mark->exempted) {
            $theoryObtained = 0;
            $practicalObtained = 0;
            $internalObtained = 0;
        }

        // Fallback maximums from ExamSubject if not explicitly specified
        if ($theoryMax === 0 && $practicalMax === 0 && $internalMax === 0 && $mark->exam_subject_id) {
            $examSubject = $mark->examSubject ?? ExamSubject::find($mark->exam_subject_id);
            if ($examSubject) {
                $theoryMax = (int) ($examSubject->theory_marks ?? 0);
                $practicalMax = (int) ($examSubject->practical_marks ?? 0);
                $mark->theory_maximum = $theoryMax;
                $mark->practical_maximum = $practicalMax;
            }
        }

        $totalObtained = $theoryObtained + $practicalObtained + $internalObtained;
        $totalMaximum = $theoryMax + $practicalMax + $internalMax;

        if ($totalMaximum === 0) {
            $totalMaximum = 100;
            $mark->total_maximum = 100;
        }

        $percentage = ($totalMaximum > 0) ? round(($totalObtained / $totalMaximum) * 100, 2) : 0;

        $examSubject = null;
        if ($mark->exam_subject_id) {
            $examSubject = $mark->relationLoaded('examSubject') ? $mark->examSubject : ExamSubject::find($mark->exam_subject_id);
        }

        $passMarks = $examSubject?->pass_marks ?? 33;
        $isPassed = ($mark->absent || $mark->exempted) ? false : ($totalObtained >= $passMarks);

        $grade = $this->calculateGrade($percentage);

        $mark->total_obtained = $totalObtained;
        $mark->total_maximum = $totalMaximum;
        $mark->percentage = $percentage;
        $mark->is_passed = $isPassed;
        $mark->grade = $grade;

        return $mark;
    }

    /**
     * Calculate grade corresponding to a percentage using GradeScale.
     */
    public function calculateGrade(float $percentage): string
    {
        return GradeScale::fromPercentage($percentage)->value;
    }

    /**
     * Bulk recalculate marks for an exam or subject.
     */
    public function bulkRecalculate(int $examId, ?int $examSubjectId = null): int
    {
        $query = ExamMark::where('exam_id', $examId);
        if ($examSubjectId) {
            $query->where('exam_subject_id', $examSubjectId);
        }

        $marks = $query->get();
        $count = 0;

        DB::transaction(function () use ($marks, &$count) {
            foreach ($marks as $mark) {
                $this->calculateMark($mark)->save();
                $count++;
            }
        });

        return $count;
    }
}