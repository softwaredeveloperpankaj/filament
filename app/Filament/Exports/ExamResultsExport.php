<?php

namespace App\Exports;

use App\Models\ExamResult;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ExamResultsExport extends Exporter
{
    protected static ?string $model = ExamResult::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('exam.name')->label('Exam'),
            ExportColumn::make('student.name')->label('Student Name'),
            ExportColumn::make('entry.roll_no')->label('Roll No'),
            ExportColumn::make('grand_total_obtained')->label('Grand Total'),
            ExportColumn::make('grand_total_maximum')->label('Max Marks'),
            ExportColumn::make('overall_percentage')->label('Overall %'),
            ExportColumn::make('overall_grade')->label('Grade'),
            ExportColumn::make('rank_in_class')->label('Class Rank'),
            ExportColumn::make('rank_in_section')->label('Section Rank'),
            ExportColumn::make('is_passed')->label('Passed'),
            ExportColumn::make('status')->label('Status'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'Exam results export has completed.';
    }
}