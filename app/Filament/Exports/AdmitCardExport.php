<?php

namespace App\Exports;

use App\Models\ExamStudentEntry;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AdmitCardExport extends Exporter
{
    protected static ?string $model = ExamStudentEntry::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('exam.name')->label('Exam'),
            ExportColumn::make('student.name')->label('Student Name'),
            ExportColumn::make('roll_no')->label('Roll No'),
            ExportColumn::make('section.name')->label('Section'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('admit_card_printed')->label('Printed'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'Admit card roster export has completed.';
    }
}