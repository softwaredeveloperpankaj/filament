<?php

namespace App\Exports;

use App\Models\ExamMark;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ExamMarksExport extends Exporter
{
    protected static ?string $model = ExamMark::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('exam.name')->label('Exam'),
            ExportColumn::make('entry.student.name')->label('Student Name'),
            ExportColumn::make('entry.roll_no')->label('Roll No'),
            ExportColumn::make('examSubject.subject.name')->label('Subject'),
            ExportColumn::make('theory_obtained')->label('Theory'),
            ExportColumn::make('practical_obtained')->label('Practical'),
            ExportColumn::make('internal_obtained')->label('Internal'),
            ExportColumn::make('total_obtained')->label('Total'),
            ExportColumn::make('percentage')->label('%'),
            ExportColumn::make('grade')->label('Grade'),
            ExportColumn::make('is_passed')->label('Passed'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'Exam marks export has completed.';
    }
}