<?php

namespace App\Filament\Exports;

use App\Models\SectionSubject;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class SectionSubjectExporter extends Exporter
{
    protected static ?string $model = SectionSubject::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('branch.name')
                ->label('Branch'),
            ExportColumn::make('branchClass.name')
                ->label('Class'),
            ExportColumn::make('section.name')
                ->label('Section'),
            ExportColumn::make('subject_display')
                ->label('Subject')
                ->state(fn (SectionSubject $record): string => trim(
                    ($record->subject?->name ?? '')
                    . ' ('
                    . ($record->subject?->code ?? '')
                    . ')'
                )),
            ExportColumn::make('teacher.user.name')
                ->label('Teacher'),
            ExportColumn::make('teacher.user.employee_id')
                ->label('Teacher Employee ID'),
            ExportColumn::make('created_at')
                ->label('Created At'),
            ExportColumn::make('updated_at')
                ->label('Updated At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your section subject export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
