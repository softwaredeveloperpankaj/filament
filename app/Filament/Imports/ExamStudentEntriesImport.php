<?php

namespace App\Imports;

use App\Models\ExamStudentEntry;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ExamStudentEntriesImport extends Importer
{
    protected static ?string $model = ExamStudentEntry::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('exam_id')->requiredMapping()->numeric(),
            ImportColumn::make('student_id')->requiredMapping()->numeric(),
            ImportColumn::make('section_id')->requiredMapping()->numeric(),
            ImportColumn::make('roll_no')->requiredMapping(),
        ];
    }

    public function resolveRecord(): ?ExamStudentEntry
    {
        return ExamStudentEntry::firstOrNew([
            'exam_id' => $this->data['exam_id'],
            'student_id' => $this->data['student_id'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        return 'Exam student entries import has completed.';
    }
}