<?php

namespace App\Filament\Imports;

use App\Models\Branch;
use App\Models\BranchClass;
use App\Models\ClassSection;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Subject;
use App\Models\TeacherProfile;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Illuminate\Validation\ValidationException;

class SectionSubjectImporter extends Importer
{
    protected static ?string $model = SectionSubject::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('branch')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->example('Demo Public School, City Center')
                ->fillRecordUsing(fn (): null => null),

            ImportColumn::make('branchClass')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->example('12th')
                ->fillRecordUsing(fn (): null => null),

            ImportColumn::make('section')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->example('B')
                ->fillRecordUsing(fn (): null => null),

            ImportColumn::make('subject')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->example('Physical Education (12TH-PED)')
                ->fillRecordUsing(fn (): null => null),

            ImportColumn::make('teacher_employee_id')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->example('TEACHER-1-0011')
                ->fillRecordUsing(fn (): null => null),
        ];
    }

    public function resolveRecord(): SectionSubject
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
                'class' => "Class [{$this->data['branchClass']}] was not found in branch [{$branch->name}].",
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

        $classSection = ClassSection::query()
            ->where('branch_id', $branch->id)
            ->where('branch_class_id', $branchClass->id)
            ->where('section_id', $section->id)
            ->first();

        if (! $classSection) {
            throw ValidationException::withMessages([
                'section' => "Class section [{$branchClass->name} - {$section->name}] does not exist in branch [{$branch->name}].",
            ]);
        }

        $subjectValue = trim($this->data['subject']);

        if (! preg_match('/^(.*)\s+\(([^()]+)\)$/', $subjectValue, $matches)) {
            throw ValidationException::withMessages([
                'subject' => "Subject must use this format: Subject Name (SUBJECT-CODE). Received [{$subjectValue}].",
            ]);
        }

        $subjectName = trim($matches[1]);

        $subjectCode = trim($matches[2]);

        $subject = Subject::query()
            ->where('branch_id', $branch->id)
            ->where('name', $subjectName)
            ->where('code', $subjectCode)
            ->first();

        if (! $subject) {
            throw ValidationException::withMessages([
                'subject' => "Subject [{$subjectName} ({$subjectCode})] was not found in branch [{$branch->name}].",
            ]);
        }

        $teacher = TeacherProfile::query()
            ->where('branch_id', $branch->id)
            ->whereHas('user', function ($query): void {
                $query->where(
                    'employee_id',
                    $this->data['teacher_employee_id'],
                );
            })
            ->first();

        if (! $teacher) {
            throw ValidationException::withMessages([
                'teacher_employee_id' => "Teacher with employee ID [{$this->data['teacher_employee_id']}] was not found in branch [{$branch->name}].",
            ]);
        }

        if (! $teacher->subjects()->whereKey($subject->id)->exists()) {
            throw ValidationException::withMessages([
                'teacher_employee_id' => "Teacher [{$teacher->user?->employee_id}] is not qualified for subject [{$subject->name}].",
            ]);
        }

        return SectionSubject::firstOrNew(
            [
                'branch_id' => $branch->id,
                'branch_class_id' => $branchClass->id,
                'section_id' => $section->id,
                'subject_id' => $subject->id,
            ],
            [
                'class_section_id' => $classSection->id,
                'teacher_profile_id' => $teacher->id,
            ],
        );
    }

    protected function beforeSave(): void
    {
        $branch = Branch::query()
            ->where('name', $this->data['branch'])
            ->firstOrFail();

        $branchClass = BranchClass::query()
            ->where('branch_id', $branch->id)
            ->where('name', $this->data['branchClass'])
            ->firstOrFail();

        $section = Section::query()
            ->where('name', $this->data['section'])
            ->firstOrFail();

        $this->record->class_section_id = ClassSection::query()
            ->where('branch_id', $branch->id)
            ->where('branch_class_id', $branchClass->id)
            ->where('section_id', $section->id)
            ->value('id');

        $teacherId = TeacherProfile::query()
            ->where('branch_id', $branch->id)
            ->whereHas('user', function ($query): void {
                $query->where(
                    'employee_id',
                    $this->data['teacher_employee_id'],
                );
            })
            ->value('id');

        $this->record->teacher_profile_id = $teacherId;
    }    

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your section subject import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
