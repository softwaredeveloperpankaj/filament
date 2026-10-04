<?php

namespace Tests\Unit;

use App\Enums\GradeScale;
use App\Models\ExamMark;
use App\Models\ExamSubject;
use App\Services\MarksCalculationService;
use PHPUnit\Framework\TestCase;

class MarksCalculationServiceTest extends TestCase
{
    public function test_calculate_grade_uses_project_grade_scale_ranges(): void
    {
        $service = new MarksCalculationService();

        $this->assertSame(GradeScale::A_PLUS->value, $service->calculateGrade(95));
        $this->assertSame(GradeScale::B_PLUS->value, $service->calculateGrade(78));
        $this->assertSame(GradeScale::C->value, $service->calculateGrade(42));
        $this->assertSame(GradeScale::F->value, $service->calculateGrade(20));
    }

    public function test_absent_mark_is_zeroed_and_failed(): void
    {
        $service = new MarksCalculationService();

        $mark = new ExamMark([
            'exam_subject_id' => 1,
            'theory_obtained' => 80,
            'theory_maximum' => 100,
            'practical_obtained' => 20,
            'practical_maximum' => 20,
            'internal_obtained' => 10,
            'internal_maximum' => 10,
            'absent' => true,
            'exempted' => false,
        ]);
        $mark->setRelation('examSubject', new ExamSubject([
            'pass_marks' => 33,
            'theory_marks' => 100,
            'practical_marks' => 20,
        ]));

        $service->calculateMark($mark);

        $this->assertSame(0, $mark->total_obtained);
        $this->assertSame(130, $mark->total_maximum);
        $this->assertSame(0.0, (float) $mark->percentage);
        $this->assertFalse($mark->is_passed);
        $this->assertSame(GradeScale::F->value, $mark->grade);
    }

    public function test_exempted_mark_is_zeroed_and_failed(): void
    {
        $service = new MarksCalculationService();

        $mark = new ExamMark([
            'exam_subject_id' => 2,
            'theory_obtained' => 65,
            'theory_maximum' => 100,
            'practical_obtained' => 25,
            'practical_maximum' => 25,
            'internal_obtained' => 5,
            'internal_maximum' => 5,
            'absent' => false,
            'exempted' => true,
        ]);
        $mark->setRelation('examSubject', new ExamSubject([
            'pass_marks' => 33,
            'theory_marks' => 100,
            'practical_marks' => 25,
        ]));

        $service->calculateMark($mark);

        $this->assertSame(0, $mark->total_obtained);
        $this->assertFalse($mark->is_passed);
        $this->assertSame(GradeScale::F->value, $mark->grade);
    }
}
