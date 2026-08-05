<?php

namespace App\Filament\Imports;

use App\Models\Branch;
use App\Models\BranchClass;
use App\Models\ClassSection;
use App\Models\FormTemplate;
use App\Models\Student;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Number;
use Illuminate\Validation\Rule;

class StudentImporter extends Importer
{
    protected static ?string $model = Student::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('admission_date')
                ->label('Admission Date')
                ->rules(['nullable', 'date']),

            ImportColumn::make('academic_year')
                ->label('Academic Year')
                ->rules(['nullable', 'string', 'max:20']),

            ImportColumn::make('form_data')
                ->label('Form Data (JSON)')
                ->castStateUsing(function (?string $state): ?array {
                    if (blank($state)) return null;
                    $decoded = json_decode($state, true);
                    return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
                })
                ->rules(['nullable']),

            ImportColumn::make('status')
                ->label('Status')
                ->rules(['nullable', 'string']),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Select::make('branch_id')
                ->label('Branch')
                ->options(Branch::pluck('name', 'id')->toArray())
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(function (Set $set) {
                    $set('branch_class_id', null);
                    $set('section_id', null);
                    $set('form_template_id', null);
                })
                ->required(),

            Select::make('branch_class_id')
                ->label('Class')
                ->options(function (Get $get): array {
                    $branchId = $get('branch_id');

                    if (! $branchId) {
                        return [];
                    }

                    return BranchClass::where('branch_id', $branchId)
                        ->pluck('name', 'id')
                        ->toArray();
                })
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('section_id', null))
                ->required(),

            Select::make('section_id')
                ->label('Section')
                ->options(function (Get $get): array {
                    $branchClassId = $get('branch_class_id');

                    if (! $branchClassId) {
                        return [];
                    }

                    return ClassSection::with('section')
                        ->where('branch_class_id', $branchClassId)
                        ->get()
                        ->mapWithKeys(fn ($item) => [
                            $item->section_id => $item->section?->name,
                        ])
                        ->toArray();
                })
                ->searchable()
                ->preload(),
        ];
    }

    public function resolveRecord(): ?Student
    {
        try {
            $branchId = $this->options['branch_id'] ?? null;

            if (! $branchId) {
                throw new RowImportFailedException('Branch is missing.');
            }

            $formTemplate = FormTemplate::query()
                ->with('activeVersion')
                ->where('branch_id', $branchId)
                ->first();

            if (! $formTemplate || ! $formTemplate->activeVersion) {
                throw new RowImportFailedException('No active form template found for the selected branch.');
            }

            $formData = $this->data['form_data'] ?? [];

            if (is_string($formData)) {
                $formData = json_decode($formData, true) ?? [];
            }

            if (! is_array($formData)) {
                throw new RowImportFailedException('Form Data must be a valid JSON array.');
            }

            // Safely extract schema_json regardless of whether Eloquent casts it to array or string
            $schemaRaw = $formTemplate->activeVersion->schema_json;
            if (is_string($schemaRaw)) {
                $schemaRaw = json_decode($schemaRaw, true) ?? [];
            }

            // Handle case where schema is wrapped in a top-level {"schema": [...]} key or direct array [...]
            $schema = $schemaRaw['schema'] ?? $schemaRaw;

            [$rules, $messages] = $this->buildFormDataValidation($schema, $formData);

            $validator = Validator::make(['form_data' => $formData], $rules, $messages);

            if ($validator->fails()) {
                $errorMessage = implode(' | ', $validator->errors()->all());
                throw new RowImportFailedException($errorMessage);
            }

            $student = new Student();
            $student->branch_id = $branchId;
            $student->branch_class_id = $this->options['branch_class_id'] ?? null;
            $student->section_id = $this->options['section_id'] ?? null;
            $student->form_template_id = $formTemplate->id;
            $student->form_data = $formData;
            $student->admission_date = $this->data['admission_date'] ?? null;
            $student->academic_year = $this->data['academic_year'] ?? null;
            $student->status = $this->data['status'] ?? 'active';

            return $student;

        } catch (RowImportFailedException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new RowImportFailedException($e->getMessage());
        }
    }

    protected function buildFormDataValidation(array $schema, array $formData): array
    {
        $rules = [];
        $messages = [];
        $expectedKeys = [];

        foreach ($schema as $section) {
            foreach (($section['fields'] ?? []) as $field) {
                if (! ($field['is_active'] ?? false)) {
                    continue;
                }

                $fieldKey = $field['field_key'] ?? null;
                if (! $fieldKey) {
                    continue;
                }

                $expectedKeys[] = $fieldKey;

                $fieldRules = [($field['is_required'] ?? false) ? 'required' : 'nullable'];

                if (! empty($field['validation_rules']) && is_array($field['validation_rules'])) {
                    foreach ($field['validation_rules'] as $rule) {
                        $fieldRules[] = $rule;
                    }
                }

                switch ($field['type'] ?? null) {
                    case 'text':
                    case 'textarea':
                    case 'email':
                        $fieldRules[] = 'string';
                        break;
                    case 'number':
                        $fieldRules[] = 'numeric';
                        break;
                    case 'date':
                        $fieldRules[] = 'date';
                        break;
                    case 'checkbox':
                        $fieldRules[] = 'boolean';
                        break;
                    case 'radio':
                    case 'select':
                        $options = collect($field['options'] ?? [])->pluck('value')->filter()->values()->all();
                        if (! empty($options)) {
                            $fieldRules[] = Rule::in($options);
                        }
                        break;
                }

                $rules["form_data.{$fieldKey}"] = array_values(array_unique($fieldRules, SORT_REGULAR));
            }
        }

        $uploadedKeys = array_keys($formData);

        $missingKeys = array_diff($expectedKeys, $uploadedKeys);
        $extraKeys = array_diff($uploadedKeys, $expectedKeys);

        if (! empty($missingKeys) || ! empty($extraKeys)) {
            $errorParts = [];

            if (! empty($missingKeys)) {
                $errorParts[] = 'Missing required schema keys: [' . implode(', ', $missingKeys) . ']';
            }

            if (! empty($extraKeys)) {
                $errorParts[] = 'Unrecognized/Invalid keys provided: [' . implode(', ', $extraKeys) . ']';
            }

            throw new RowImportFailedException('Schema mismatch error: ' . implode(' | ', $errorParts));
        }

        $rules['form_data'] = ['required', 'array', 'array:' . implode(',', $expectedKeys)];

        return [$rules, $messages];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your student import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
