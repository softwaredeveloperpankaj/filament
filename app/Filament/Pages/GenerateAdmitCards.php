<?php

namespace App\Filament\Pages;

use App\Enums\ExamEntryStatus;
use App\Models\BranchClass;
use App\Models\Exam;
use App\Models\ExamStudentEntry;
use App\Models\Section;
use App\Services\AdmitCardService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class GenerateAdmitCards extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;
    protected static string|\UnitEnum|null $navigationGroup = 'Examinations';
    protected static ?int $navigationSort = 11;
    protected static ?string $title = 'Bulk Admit Cards';

    protected string $view = 'filament.pages.generate-admit-cards';

    public ?int $exam_id = null;
    public ?int $section_id = null;
    public array $entries = [];

    public function updatedExamId(): void
    {
        $this->loadEntries();
    }

    public function updatedSectionId(): void
    {
        $this->loadEntries();
    }

    public function loadEntries(): void
    {
        if (!$this->exam_id) {
            $this->entries = [];
            return;
        }

        $query = ExamStudentEntry::with(['student', 'exam'])
            ->where('exam_id', $this->exam_id);

        if ($this->section_id) {
            $query->where('section_id', $this->section_id);
        }

        $records = $query->get();

        $this->entries = $records->map(fn($e) => [
            'id' => $e->id,
            'roll_no' => $e->roll_no,
            'student_name' => $e->student?->name ?? 'Student #' . $e->student_id,
            'status' => $e->status instanceof ExamEntryStatus ? $e->status->getLabel() : (string) $e->status,
            'admit_card_printed' => (bool) $e->admit_card_printed,
        ])->toArray();
    }

    public function generateAll(): void
    {
        if (!$this->exam_id) {
            return;
        }

        $query = ExamStudentEntry::where('exam_id', $this->exam_id);
        if ($this->section_id) {
            $query->where('section_id', $this->section_id);
        }

        $records = $query->get();
        foreach ($records as $entry) {
            $entry->generateAdmitCardData();
            $entry->update(['status' => ExamEntryStatus::ADMIT_CARD_GENERATED]);
        }

        $this->loadEntries();

        Notification::make()
            ->title('Admit Cards Generated')
            ->body("Generated admit card data for {$records->count()} students.")
            ->success()
            ->send();
    }

    public function downloadPdf()
    {
        if (!$this->exam_id) {
            return null;
        }

        $query = ExamStudentEntry::where('exam_id', $this->exam_id);
        if ($this->section_id) {
            $query->where('section_id', $this->section_id);
        }

        $records = $query->get();
        if ($records->isEmpty()) {
            Notification::make()->title('No entries found')->warning()->send();
            return null;
        }

        AdmitCardService::markPrinted($records);
        $pdf = AdmitCardService::bulkPdf($records);

        return response()->streamDownload(
            fn () => print($pdf),
            "admit-cards-exam-{$this->exam_id}.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }
}