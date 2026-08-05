<?php

namespace App\Filament\Exports;

use App\Models\Student;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class StudentExporter extends Exporter
{
    protected static ?string $model = Student::class;

    public static function getColumns(): array
    {
        $columns = [
            ExportColumn::make('academic_year'),
            ExportColumn::make('admission_date'),
            ExportColumn::make('status'),
            ExportColumn::make('form_data')
                ->label('Form Data (JSON)')
                ->getStateUsing(fn (Student $record): ?string => 
                    $record->form_data ? json_encode($record->form_data, JSON_UNESCAPED_UNICODE) : null
                ),            
        ];

        return $columns;
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your student export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
