<?php

namespace App\Filament\Exports;

use App\Models\FormTemplate;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class FormTemplateExporter extends Exporter
{
    protected static ?string $model = FormTemplate::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('branch.name'),
            ExportColumn::make('active_version_id'),
            ExportColumn::make('user.name'),
            ExportColumn::make('registration_serial'),
            ExportColumn::make('name'),
            ExportColumn::make('slug'),
            ExportColumn::make('type'),
            ExportColumn::make('status'),
            ExportColumn::make('is_active'),
            ExportColumn::make('form_layout'),
            ExportColumn::make('rollno_generation_scope'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your form template export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
