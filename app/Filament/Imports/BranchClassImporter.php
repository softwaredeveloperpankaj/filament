<?php

namespace App\Filament\Imports;

use App\Models\BranchClass;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class BranchClassImporter extends Importer
{
    protected static ?string $model = BranchClass::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('branch_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'alpha_num', 'max:255']),
            ImportColumn::make('starting_roll_no')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'alpha_dash']),
        ];
    }

    public function resolveRecord(): BranchClass
    {
        return BranchClass::firstOrNew([
            'branch_id' => $this->data['branch_id'],
            'name' => $this->data['name'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your branch class import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
