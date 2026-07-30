<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Filament\Resources\Students\Concerns\BuildsDynamicFormFields;
use App\Models\Branch;
use App\Models\BranchClass;
use App\Models\ClassSection;
use App\Models\FormTemplate;
use App\Models\Section as SectionModel;
use App\Models\Student;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentForm
{
    use BuildsDynamicFormFields;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Branch & Class')
                    ->schema([
                        Select::make('branch_id')
                            ->label('Branch')
                            ->options(Branch::pluck('name', 'id'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($set) {
                                $set('branch_class_id', null);
                                $set('section_id', null);
                                $set('form_template_id', null);
                                $set('roll_no', null);
                            }),

                        Select::make('branch_class_id')
                            ->label('Class')
                            ->options(
                                fn($get) =>
                                $get('branch_id')
                                    ? BranchClass::where('branch_id', $get('branch_id'))->pluck('name', 'id')
                                    : []
                            )
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($set, $get) {
                                $set('section_id', null);
                                $set('roll_no', static::previewRollNo(
                                    (int) $get('form_template_id'),
                                    (int) $get('branch_class_id'),
                                    null
                                ));
                            }),

                        Select::make('section_id')
                            ->label('Section')
                            ->options(
                                fn($get) =>
                                $get('branch_class_id')
                                    ? ClassSection::where('branch_class_id', $get('branch_class_id'))
                                    ->with('section')
                                    ->get()
                                    ->pluck('section.name', 'id')
                                    : []
                            )
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($set, $get) {
                                $classSection = ClassSection::find($get('section_id'));
                                $set('roll_no', static::previewRollNo(
                                    (int) $get('form_template_id'),
                                    (int) $get('branch_class_id'),
                                    $classSection?->section_id
                                ));
                            }),

                        Select::make('form_template_id')
                            ->label('Form Template')
                            ->options(
                                fn($get) =>
                                $get('branch_id')
                                    ? FormTemplate::query()
                                    ->where('branch_id', $get('branch_id'))
                                    ->where('status', 'published')
                                    ->where('is_active', true)
                                    ->pluck('name', 'id')
                                    : []
                            )
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($set, $get) {
                                $classSection = ClassSection::find($get('section_id'));
                                $set('roll_no', static::previewRollNo(
                                    (int) $get('form_template_id'),
                                    (int) $get('branch_class_id'),
                                    $classSection?->section_id
                                ));
                            }),

                        TextInput::make('academic_year')
                            ->label('Academic Year')
                            ->default(now()->year . '-' . now()->addYear()->format('y'))
                            ->required()
                            ->readOnly()
                            ->dehydrated(true),

                        TextInput::make('roll_no')
                            ->label('Roll Number (Preview)')
                            ->placeholder('Select class/section & template above')
                            ->helperText('Auto-generated on save. This is a preview only.')
                            ->readOnly()
                            ->dehydrated(false),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('More Details')
                    ->key(fn($get) => 'student-fields-' . ($get('form_template_id') ?? 'none'))
                    ->schema(fn($get) => static::getDynamicFormComponents($get('form_template_id')) ?: [
                        TextEntry::make('hint')
                            ->hiddenLabel()
                            ->default('Select a branch and form template above to load more fields.'),
                    ])
                    ->statePath('form_data')
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    // ─── Roll No Preview ──────────────────────────────────────────────────────

    protected static function previewRollNo(
        ?int $formTemplateId,
        ?int $branchClassId,
        ?int $sectionId
    ): ?string {
        if (!$formTemplateId && !$branchClassId) {
            return null;
        }

        $template = $formTemplateId ? FormTemplate::find($formTemplateId) : null;
        $scope    = $template?->rollno_generation_scope;

        switch ($scope) {

            case 'section':
                if (!$sectionId) return null;
                $source    = SectionModel::find($sectionId);
                $startFrom = (int) ($source?->starting_roll_no ?? 1);
                $count     = Student::withTrashed()
                    ->where('section_id', $sectionId)
                    ->whereNotNull('roll_no')
                    ->count();
                break;

            case 'branch_class':
                if (!$branchClassId) return null;
                $source    = BranchClass::find($branchClassId);
                $startFrom = (int) ($source?->starting_roll_no ?? 1);
                $count     = Student::withTrashed()
                    ->where('branch_class_id', $branchClassId)
                    ->whereNotNull('roll_no')
                    ->count();
                break;

            default:
                if (!$branchClassId) return null;
                $startFrom = 1;
                $count     = Student::withTrashed()
                    ->where('branch_class_id', $branchClassId)
                    ->whereNotNull('roll_no')
                    ->count();
                break;
        }

        return (string) ($startFrom + $count);
    }
}
