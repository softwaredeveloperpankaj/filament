<?php

namespace App\Filament\Imports;

use App\Models\FormTemplate;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class FormTemplateImporter extends Importer
{
    protected static ?string $model = FormTemplate::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('branch')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('active_version_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('user')
                ->relationship(),
            ImportColumn::make('registration_serial')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('slug')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('type')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('status')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('is_active')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('form_layout')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('rollno_generation_scope')
                ->requiredMapping()
                ->rules(['required']),
        ];
    }

    public function resolveRecord(): FormTemplate
    {
        return FormTemplate::firstOrNew([
            'id' => $this->data['id'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your form template import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
