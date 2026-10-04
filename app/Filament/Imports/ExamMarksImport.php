<?php

namespace App\Imports;

use App\Models\ExamMark;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ExamMarksImport extends Importer
{
    protected static ?string $model = ExamMark::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('exam_id')->requiredMapping()->numeric(),
            ImportColumn::make('exam_student_entry_id')->requiredMapping()->numeric(),
            ImportColumn::make('exam_subject_id')->requiredMapping()->numeric(),
            ImportColumn::make('theory_obtained')->numeric(),
            ImportColumn::make('practical_obtained')->numeric(),
            ImportColumn::make('internal_obtained')->numeric(),
        ];
    }

    public function resolveRecord(): ?ExamMark
    {
        return ExamMark::firstOrNew([
            'exam_id' => $this->data['exam_id'],
            'exam_student_entry_id' => $this->data['exam_student_entry_id'],
            'exam_subject_id' => $this->data['exam_subject_id'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        return 'Exam marks import has completed.';
    }
}