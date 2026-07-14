<?php

namespace App\Filament\Resources\SectionSubjects\Schemas;

use App\Models\BranchClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeacherProfile;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
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
                    ->required(),

                Select::make('branch_class_id')
                    ->label('Class')
                    ->options(fn(Get $get) => BranchClass::where('branch_id', $get('branch_id'))->pluck('name','id'))
                    ->live()
                    ->required()
                    ->disabled(fn(Get $get) => !$get('branch_id')),

                Select::make('section_id')
                    ->label('Section')
                    ->options(fn(Get $get) => Section::where('branch_id', $get('branch_id'))->pluck('name','id'))
                    ->live()
                    ->required()
                    ->disabled(fn(Get $get) => !$get('branch_class_id')),

                Select::make('subject_id')
                    ->label('Subject')
                    ->options(fn(Get $get) => Subject::where('branch_id', $get('branch_id'))->pluck('name','id'))
                    ->live()
                    ->required()
                    ->disabled(fn(Get $get) => !$get('section_id')),

                Select::make('teacher_profile_id')
                    ->label('Teacher')
                    ->options(fn(Get $get) => TeacherProfile::where('branch_id', $get('branch_id'))
                    ->with('user')->get()->pluck('user.name','id'))
                    ->nullable()
                    ->disabled(fn(Get $get) => !$get('subject_id')),

            ]);
    }
}
