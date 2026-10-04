<?php

namespace App\Filament\Resources\ExamMarks\Pages;

use App\Filament\Resources\ExamMarks\ExamMarkResource;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\ExamStudentEntry;
use App\Models\ExamSubject;
use App\Services\MarksCalculationService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class EnterMarks extends Page
{
    protected static string $resource = ExamMarkResource::class;

    protected string $view = 'filament.resources.exam-marks.pages.enter-marks';

    protected static ?string $title = 'Bulk Marks Entry';

    public ?int $exam_id = null;
    public ?int $exam_subject_id = null;
    public array $marksData = [];

    public function mount(): void
    {
        $this->exam_id = request()->query('exam_id') ? (int) request()->query('exam_id') : null;
        $this->exam_subject_id = request()->query('exam_subject_id') ? (int) request()->query('exam_subject_id') : null;

        if ($this->exam_id && $this->exam_subject_id) {
            $this->loadEntries();
        }
    }

    public function updatedExamId(): void
    {
        $this->exam_subject_id = null;
        $this->marksData = [];
    }

    public function updatedExamSubjectId(): void
    {
        $this->loadEntries();
    }

    public function loadEntries(): void
    {
        if (!$this->exam_id || !$this->exam_subject_id) {
            $this->marksData = [];
            return;
        }

        $entries = ExamStudentEntry::with('student')
            ->where('exam_id', $this->exam_id)
            ->get();

        $existingMarks = ExamMark::where('exam_id', $this->exam_id)
            ->where('exam_subject_id', $this->exam_subject_id)
            ->get()
            ->keyBy('exam_student_entry_id');

        $examSubject = ExamSubject::find($this->exam_subject_id);

        $this->marksData = [];
        foreach ($entries as $entry) {
            $mark = $existingMarks->get($entry->id);

            $this->marksData[$entry->id] = [
                'entry_id' => $entry->id,
                'student_name' => $entry->student?->name ?? 'Student #' . $entry->student_id,
                'roll_no' => $entry->roll_no,
                'theory_obtained' => $mark?->theory_obtained ?? 0,
                'theory_max' => $examSubject?->theory_marks ?? 80,
                'practical_obtained' => $mark?->practical_obtained ?? 0,
                'practical_max' => $examSubject?->practical_marks ?? 20,
                'internal_obtained' => $mark?->internal_obtained ?? 0,
                'total_obtained' => $mark?->total_obtained ?? 0,
                'percentage' => $mark?->percentage ?? 0,
                'grade' => $mark?->grade ?? '—',
                'is_passed' => $mark?->is_passed ?? false,
                'is_locked' => $mark?->is_locked ?? false,
            ];
        }
    }

    public function saveMarks(): void
    {
        if (!$this->exam_id || !$this->exam_subject_id) {
            return;
        }

        $service = new MarksCalculationService();
        $savedCount = 0;

        foreach ($this->marksData as $entryId => $row) {
            if (!empty($row['is_locked'])) {
                continue;
            }

            $mark = ExamMark::firstOrNew([
                'exam_id' => $this->exam_id,
                'exam_student_entry_id' => $entryId,
                'exam_subject_id' => $this->exam_subject_id,
            ]);

            $mark->theory_obtained = (int) ($row['theory_obtained'] ?? 0);
            $mark->theory_maximum = (int) ($row['theory_max'] ?? 80);
            $mark->practical_obtained = (int) ($row['practical_obtained'] ?? 0);
            $mark->practical_maximum = (int) ($row['practical_max'] ?? 20);
            $mark->internal_obtained = (int) ($row['internal_obtained'] ?? 0);
            $mark->graded_by = Auth::id();
            $mark->graded_at = now();

            $service->calculateMark($mark);
            $mark->save();

            $this->marksData[$entryId]['total_obtained'] = $mark->total_obtained;
            $this->marksData[$entryId]['percentage'] = $mark->percentage;
            $this->marksData[$entryId]['grade'] = $mark->grade;
            $this->marksData[$entryId]['is_passed'] = $mark->is_passed;

            $savedCount++;
        }

        Notification::make()
            ->title('Marks Saved')
            ->body("Successfully saved marks for {$savedCount} students.")
            ->success()
            ->send();
    }
}