<?php

namespace App\Filament\Resources\SectionSubjects\Schemas;

use App\Models\BranchClass;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Subject;
use App\Models\TeacherProfile;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class SectionSubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('branch_class_id', null);
                        $set('section_id', null);
                        $set('subject_id', null);
                        $set('teacher_profile_id', null);
                    })
                    ->required(),

                Select::make('branch_class_id')
                    ->label('Class')
                    ->options(fn (Get $get) => BranchClass::where('branch_id', $get('branch_id'))
                        ->pluck('name', 'id'))
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('section_id', null);
                        $set('subject_id', null);
                        $set('teacher_profile_id', null);
                    })
                    ->required()
                    ->disabled(fn (Get $get) => ! $get('branch_id')),

                Select::make('section_id')
                    ->label('Section')
                    ->options(fn (Get $get) => Section::where('branch_id', $get('branch_id'))
                        ->pluck('name', 'id'))
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('subject_id', null);
                        $set('teacher_profile_id', null);
                    })
                    ->required()
                    ->disabled(fn (Get $get) => ! $get('branch_class_id')),

                // Select::make('subject_id')
                //     ->label('Subject')
                //     ->options(fn (Get $get) => Subject::where('branch_id', $get('branch_id'))
                //         ->get()
                //         ->mapWithKeys(fn ($subject) => [
                //             $subject->id => "{$subject->name} ({$subject->code})",
                //         ])
                //     )
                //     ->live()
                //     ->afterStateUpdated(fn (Set $set) => $set('teacher_profile_id', null))
                //     ->required()
                //     ->disabled(fn (Get $get) => ! $get('section_id')),
                Select::make('subject_id')
                    ->label('Subject')
                    ->searchable()
                    ->options(function (Get $get, ?SectionSubject $record): array {
                        $branchId = $get('branch_id');
                        $branchClassId = $get('branch_class_id');
                        $sectionId = $get('section_id');

                        if (blank($branchId) || blank($branchClassId) || blank($sectionId)) {
                            return [];
                        }

                        $assignedSubjectIds = SectionSubject::query()
                            ->where('branch_id', $branchId)
                            ->where('branch_class_id', $branchClassId)
                            ->where('section_id', $sectionId)
                            ->when(
                                $record,
                                fn ($query) => $query->whereKeyNot($record->getKey()),
                            )
                            ->pluck('subject_id');

                        return Subject::query()
                            ->where('branch_id', $branchId)
                            ->whereNotIn('id', $assignedSubjectIds)
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn (Subject $subject): array => [
                                $subject->id => "{$subject->name} ({$subject->code})",
                            ])
                            ->all();
                    })
                    ->getOptionLabelUsing(function ($value): ?string {
                        $subject = Subject::find($value);

                        return $subject
                            ? "{$subject->name} ({$subject->code})"
                            : null;
                    })
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('teacher_profile_id', null))
                    ->required()
                    ->disabled(fn (Get $get): bool =>
                        blank($get('branch_id'))
                        || blank($get('branch_class_id'))
                        || blank($get('section_id'))
                    ),
                    
                Select::make('teacher_profile_id')
                    ->label('Teacher')
                    ->options(function (Get $get, ?SectionSubject $record): array {
                        $branchId = $get('branch_id');
                        $branchClassId = $get('branch_class_id');
                        $sectionId = $get('section_id');
                        $subjectId = $get('subject_id');

                        if (
                            blank($branchId)
                            || blank($branchClassId)
                            || blank($sectionId)
                            || blank($subjectId)
                        ) {
                            return [];
                        }

                        $assignedTeacherIds = SectionSubject::query()
                            ->where('branch_id', $branchId)
                            ->where('branch_class_id', $branchClassId)
                            ->where('section_id', $sectionId)
                            // ->where('subject_id', $subjectId)
                            ->when(
                                $record,
                                fn ($query) => $query->whereKeyNot($record->getKey()),
                            )
                            ->whereNotNull('teacher_profile_id')
                            ->pluck('teacher_profile_id');

                        return TeacherProfile::query()
                            ->where('branch_id', $branchId)
                            ->where('status', 'active')
                            ->whereHas(
                                'subjects',
                                fn ($query) => $query->whereKey($subjectId),
                            )
                            ->whereNotIn('id', $assignedTeacherIds)
                            ->with('user')
                            ->orderBy('employee_id')
                            ->get()
                            ->mapWithKeys(fn (TeacherProfile $teacher): array => [
                                $teacher->id => sprintf(
                                    '%s (%s)',
                                    $teacher->user?->name ?? 'Unknown teacher',
                                    $teacher->user?->employee_id ?? 'No employee ID',
                                ),
                            ])
                            ->all();
                    })
                    ->getOptionLabelUsing(function ($value): ?string {
                        $teacher = TeacherProfile::query()
                            ->with('user')
                            ->find($value);

                        if (! $teacher) {
                            return null;
                        }

                        return sprintf(
                            '%s (%s)',
                            $teacher->user?->name ?? 'Unknown teacher',
                            $teacher->user?->employee_id ?? 'No employee ID',
                        );
                    })                    
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->disabled(fn (Get $get): bool =>
                        blank($get('branch_id'))
                        || blank($get('branch_class_id'))
                        || blank($get('section_id'))
                        || blank($get('subject_id'))
                    )

            ]);
    }
}