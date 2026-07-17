<?php

namespace App\Filament\Resources\SectionSubjects\Schemas;

use App\Models\BranchClass;
use App\Models\Section;
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

                Select::make('subject_id')
                    ->label('Subject')
                    ->options(fn (Get $get) => Subject::where('branch_id', $get('branch_id'))
                        ->pluck('name', 'id'))
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('teacher_profile_id', null))
                    ->required()
                    ->disabled(fn (Get $get) => ! $get('section_id')),

                Select::make('teacher_profile_id')
                    ->label('Teacher')
                    ->options(function (Get $get) {
                        $branchId = $get('branch_id');
                        $subjectId = $get('subject_id');

                        if (! $branchId || ! $subjectId) {
                            return [];
                        }

                        return TeacherProfile::query()
                            ->where('branch_id', $branchId)
                            ->where('subject_id', $subjectId)
                            ->with('user')
                            ->get()
                            ->mapWithKeys(fn ($teacher) => [
                                $teacher->id => $teacher->user?->name
                                    ? $teacher->user->name . ' (' . ($teacher->employee_id ?? 'No ID') . ')'
                                    : 'Unknown Teacher'
                            ])
                            ->toArray();
                    })
                    ->searchable()
                    ->nullable()
                    ->disabled(fn (Get $get) => ! $get('subject_id')),

            ]);
    }
}