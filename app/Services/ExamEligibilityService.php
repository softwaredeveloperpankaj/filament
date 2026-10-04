<?php

namespace App\Services;

use App\Enums\ExamEntryStatus;
use App\Models\Exam;
use App\Models\ExamStudentEntry;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ExamEligibilityService
{
    /**
     * Check if a student is eligible for an exam.
     */
    public function isStudentEligible(Exam $exam, Student $student): bool
    {
        // Must match branch
        if ($student->branch_id !== $exam->branch_id) {
            return false;
        }

        // Must match class
        if ($student->branch_class_id !== $exam->branch_class_id) {
            return false;
        }

        // If exam is scoped to a specific section
        if ($exam->section_id && $student->section_id !== $exam->section_id) {
            return false;
        }

        // Student must be confirmed
        if (isset($student->status) && $student->status !== 'confirmed') {
            return false;
        }

        return true;
    }

    /**
     * Auto-enroll all eligible students into the exam.
     * Returns count of newly enrolled students.
     */
    public function enrollEligibleStudents(Exam $exam): int
    {
        $query = Student::where('branch_id', $exam->branch_id)
            ->where('branch_class_id', $exam->branch_class_id);

        if ($exam->section_id) {
            $query->where('section_id', $exam->section_id);
        }

        $query->where('status', 'confirmed');

        $students = $query->get();
        $enrolledCount = 0;

        DB::transaction(function () use ($exam, $students, &$enrolledCount) {
            foreach ($students as $student) {
                $exists = ExamStudentEntry::where('exam_id', $exam->id)
                    ->where('student_id', $student->id)
                    ->exists();

                if (!$exists) {
                    ExamStudentEntry::create([
                        'exam_id' => $exam->id,
                        'student_id' => $student->id,
                        'section_id' => $student->section_id ?? $exam->section_id,
                        'roll_no' => (string) ($student->roll_no ?? $student->registration_number ?? $student->id),
                        'status' => ExamEntryStatus::ENROLLED,
                        'admit_card_printed' => false,
                    ]);
                    $enrolledCount++;
                }
            }
        });

        return $enrolledCount;
    }

    /**
     * Update status of an individual exam student entry.
     */
    public function updateEntryStatus(ExamStudentEntry $entry, ExamEntryStatus $status): bool
    {
        return $entry->update(['status' => $status]);
    }

    /**
     * Bulk update status for multiple entries.
     */
    public function bulkUpdateStatus(iterable $entries, ExamEntryStatus $status): int
    {
        $count = 0;
        foreach ($entries as $entry) {
            if ($entry instanceof ExamStudentEntry) {
                $entry->update(['status' => $status]);
                $count++;
            } elseif (is_numeric($entry)) {
                ExamStudentEntry::where('id', $entry)->update(['status' => $status]);
                $count++;
            }
        }

        return $count;
    }
}