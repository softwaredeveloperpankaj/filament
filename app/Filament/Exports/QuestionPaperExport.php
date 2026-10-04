<?php

namespace App\Exports;

use App\Models\ExamQuestionPaper;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class QuestionPaperExport extends Exporter
{
    protected static ?string $model = ExamQuestionPaper::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('exam.name')->label('Exam'),
            ExportColumn::make('examSubject.subject.name')->label('Subject'),
            ExportColumn::make('total_marks')->label('Total Marks'),
            ExportColumn::make('is_shuffled')->label('Shuffled'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'Question papers export has completed.';
    }
}