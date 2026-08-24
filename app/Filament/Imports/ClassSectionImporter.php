<?php

namespace App\Filament\Imports;

use App\Models\Branch;
use App\Models\BranchClass;
use App\Models\ClassSection;
use App\Models\Section;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;
use Illuminate\Validation\ValidationException;

class ClassSectionImporter extends Importer
{
    protected static ?string $model = ClassSection::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('branch')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->example('Demo Public School, City Center')
                ->fillRecordUsing(fn (): null => null),

            ImportColumn::make('branchClass')
                ->label('Class')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->example('12th')
                ->fillRecordUsing(fn (): null => null),

            ImportColumn::make('section')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->example('B')
                ->fillRecordUsing(fn (): null => null),
        ];
    }

    public function resolveRecord(): ClassSection
    {
        $branch = Branch::query()
            ->where('name', $this->data['branch'])
            ->first();

        if (! $branch) {
            throw ValidationException::withMessages([
                'branch' => "Branch [{$this->data['branch']}] was not found.",
            ]);
        }

        $branchClass = BranchClass::query()
            ->where('branch_id', $branch->id)
            ->where('name', $this->data['branchClass'])
            ->first();

        if (! $branchClass) {
            throw ValidationException::withMessages([
                'branchClass' => "Class [{$this->data['branchClass']}] was not found in branch [{$branch->name}].",
            ]);
        }

        $section = Section::query()
            ->where('name', $this->data['section'])
            ->first();

        if (! $section) {
            throw ValidationException::withMessages([
                'section' => "Section [{$this->data['section']}] was not found.",
            ]);
        }

        return ClassSection::firstOrNew([
            'branch_id' => $branch->id,
            'branch_class_id' => $branchClass->id,
            'section_id' => $section->id,
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your class section import has completed and '
            . Number::format($import->successful_rows)
            . ' '
            . str('row')->plural($import->successful_rows)
            . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '
                . Number::format($failedRowsCount)
                . ' '
                . str('row')->plural($failedRowsCount)
                . ' failed to import.';
        }

        return $body;
    }
}