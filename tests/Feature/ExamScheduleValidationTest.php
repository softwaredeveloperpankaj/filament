<?php

namespace Tests\Feature;

use App\Filament\Resources\ExamSchedules\Schemas\ExamScheduleForm;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\BranchClass;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\ExamSubject;
use App\Models\School;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ExamScheduleValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_reference_lists_exams_even_when_user_has_no_branch(): void
    {
        $user = User::factory()->create(['branch_id' => null]);
        $this->actingAs($user);

        $school = School::create([
            'user_id' => $user->id,
            'name' => 'Test School',
            'address' => 'Main Street',
            'phone' => '1111111111',
            'email' => 'school@example.com',
            'domain_name' => 'testschool.local',
        ]);

        $branch = Branch::create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'name' => 'Main Branch',
            'code' => 'MB',
            'address' => 'Branch address',
            'phone' => '2222222222',
            'email' => 'branch@example.com',
            'is_main' => true,
            'is_active' => true,
        ]);

        $academicYear = AcademicYear::create([
            'name' => '2026-2027',
            'start_date' => '2026-04-01',
            'end_date' => '2027-03-31',
            'is_current' => true,
            'status' => 'active',
        ]);

        $branchClass = BranchClass::create([
            'branch_id' => $branch->id,
            'name' => 'Grade 10',
            'starting_roll_no' => 1,
        ]);

        $section = Section::create([
            'branch_id' => $branch->id,
            'name' => 'A',
            'starting_roll_no' => 1,
        ]);

        $exam = Exam::create([
            'branch_id' => $branch->id,
            'branch_class_id' => $branchClass->id,
            'section_id' => $section->id,
            'name' => 'Quarterly Exam',
            'slug' => 'quarterly-exam-2026',
            'mode' => 'offline',
            'academic_year_id' => $academicYear->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-20',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'status' => 'draft',
            'instructions' => 'Read the questions carefully.',
            'is_practical' => false,
            'created_by' => $user->id,
        ]);

        $query = ExamScheduleForm::examOptionsQuery($user->branch_id);

        $this->assertTrue($query->whereKey($exam->id)->exists());
    }

    public function test_duplicate_subject_schedule_for_the_same_exam_is_rejected(): void
    {
        $user = User::factory()->create();

        $school = School::create([
            'user_id' => $user->id,
            'name' => 'Test School',
            'address' => 'Main Street',
            'phone' => '1111111111',
            'email' => 'school@example.com',
            'domain_name' => 'testschool.local',
        ]);

        $branch = Branch::create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'name' => 'Main Branch',
            'code' => 'MB',
            'address' => 'Branch address',
            'phone' => '2222222222',
            'email' => 'branch@example.com',
            'is_main' => true,
            'is_active' => true,
        ]);

        $user->update(['branch_id' => $branch->id]);

        $academicYear = AcademicYear::create([
            'name' => '2026-2027',
            'start_date' => '2026-04-01',
            'end_date' => '2027-03-31',
            'is_current' => true,
            'status' => 'active',
        ]);

        $branchClass = BranchClass::create([
            'branch_id' => $branch->id,
            'name' => 'Grade 10',
            'starting_roll_no' => 1,
        ]);

        $section = Section::create([
            'branch_id' => $branch->id,
            'name' => 'A',
            'starting_roll_no' => 1,
        ]);

        $subject = Subject::create([
            'branch_id' => $branch->id,
            'name' => 'English',
            'code' => 'ENG',
        ]);

        $exam = Exam::create([
            'branch_id' => $branch->id,
            'branch_class_id' => $branchClass->id,
            'section_id' => $section->id,
            'name' => 'Mid Term',
            'slug' => 'mid-term-2026',
            'mode' => 'offline',
            'academic_year_id' => $academicYear->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-20',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'status' => 'draft',
            'instructions' => 'Read the questions carefully.',
            'is_practical' => false,
            'created_by' => $user->id,
        ]);

        $examSubject = ExamSubject::create([
            'exam_id' => $exam->id,
            'subject_id' => $subject->id,
            'max_marks' => 100,
            'pass_marks' => 33,
            'theory_marks' => 80,
            'practical_marks' => 20,
            'duration_minutes' => 120,
            'paper_structure' => ['mcq' => 20, 'short_answer' => 30, 'long_answer' => 50],
            'is_graded' => true,
            'display_order' => 1,
        ]);

        ExamSchedule::create([
            'exam_id' => $exam->id,
            'exam_subject_id' => $examSubject->id,
            'exam_date' => '2026-10-05',
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'room_number' => 'A-101',
            'instructions' => 'Use blue pen.',
        ]);

        $this->expectException(ValidationException::class);

        ExamSchedule::create([
            'exam_id' => $exam->id,
            'exam_subject_id' => $examSubject->id,
            'exam_date' => '2026-10-05',
            'start_time' => '09:30:00',
            'end_time' => '11:30:00',
            'room_number' => 'A-102',
            'instructions' => 'Duplicate schedule.',
        ]);
    }
}
