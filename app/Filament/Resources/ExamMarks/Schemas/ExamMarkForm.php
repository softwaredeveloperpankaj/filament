<?php

namespace App\Filament\Resources\ExamMarks\Schemas;

use App\Enums\GradeScale;
use App\Enums\MarksSource;
use App\Models\ExamMark;
use App\Models\ExamStudentEntry;
use App\Models\ExamSubject;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ExamMarkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Reference')
                    ->schema([
                        Grid::make(3)->schema([

                            Select::make('exam_id')
                                ->label('Exam')
                                ->relationship('exam', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->reactive()
                                ->disabled(fn($record) => $record !== null)
                                ->afterStateUpdated(fn(callable $set) => $set('exam_subject_id', null)),

                            Select::make('exam_subject_id')
                                ->label('Subject')
                                ->options(fn(Get $get) => $get('exam_id')
                                    ? ExamSubject::query()
                                        ->where('exam_id', $get('exam_id'))
                                        ->with('subject')
                                        ->get()
                                        ->pluck('subject.name', 'id')
                                    : [])
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->reactive()
                                ->disabled(fn($record) => $record !== null)
                                ->afterStateUpdated(fn(callable $set) => $set('exam_student_entry_id', null)),

                            Select::make('exam_student_entry_id')
                                ->label('Student')
                                ->options(fn(Get $get) => $get('exam_id')
                                    ? \App\Models\ExamStudentEntry::query()
                                        ->where('exam_id', $get('exam_id'))
                                        ->with('student')
                                        ->get()
                                        ->mapWithKeys(fn($e) => [$e->id => "{$e->student->name} ({$e->roll_no})"])
                                    : [])
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live() // ← needed so hint recalculates when this or exam_subject_id changes
                                ->disabled(fn($record) => $record !== null)
                                ->hint(function (Get $get, $record) {
                                    // Only check on create — editing an existing record is fine
                                    if ($record) {
                                        return null;
                                    }

                                    $entryId = $get('exam_student_entry_id');
                                    $subjectId = $get('exam_subject_id');

                                    if (!$entryId || !$subjectId) {
                                        return null;
                                    }

                                    $exists = ExamMark::where('exam_student_entry_id', $entryId)
                                        ->where('exam_subject_id', $subjectId)
                                        ->exists();

                                    return $exists ? '⚠️ Marks already exist for this student in this subject.' : null;
                                })
                                ->hintColor('danger'),                          
                        ]),
                    ]),

                Section::make('Marks Entry')
                    ->description(fn($record) => $record?->is_locked
                        ? '🔒 These marks are locked and cannot be edited.'
                        : 'Enter theory, practical, and internal marks as applicable.')
                    ->schema([
                        Grid::make(3)->schema([

                            TextInput::make('theory_obtained')
                                ->label('Theory Obtained')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(fn(Get $get) => $get('theory_maximum') ?? 100)
                                ->default(0)
                                ->required()
                                ->disabled(fn($record) => $record?->is_locked ?? false),

                            TextInput::make('theory_maximum')
                                ->label('Theory Max')
                                ->numeric()
                                ->minValue(0)
                                ->default(fn(Get $get) => ExamSubject::find($get('exam_subject_id'))?->theory_marks ?? 0)
                                ->required()
                                ->disabled(fn($record) => $record?->is_locked ?? false),

                            TextInput::make('practical_obtained')
                                ->label('Practical Obtained')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(fn(Get $get) => $get('practical_maximum') ?? 100)
                                ->default(0)
                                ->disabled(fn($record) => $record?->is_locked ?? false),

                            TextInput::make('practical_maximum')
                                ->label('Practical Max')
                                ->numeric()
                                ->minValue(0)
                                ->default(fn(Get $get) => ExamSubject::find($get('exam_subject_id'))?->practical_marks ?? 0)
                                ->disabled(fn($record) => $record?->is_locked ?? false),

                            TextInput::make('internal_obtained')
                                ->label('Internal/Assignment Obtained')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(fn(Get $get) => $get('internal_maximum') ?? 100)
                                ->default(0)
                                ->disabled(fn($record) => $record?->is_locked ?? false),

                            TextInput::make('internal_maximum')
                                ->label('Internal/Assignment Max')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->disabled(fn($record) => $record?->is_locked ?? false),
                        ]),
                    ]),

                Section::make('Grading')
                    ->description('Grade is entered manually by the teacher/principal — it is not auto-calculated from percentage.')
                    ->schema([
                        Grid::make(2)->schema([

                            Select::make('grade')
                                ->label('Grade')
                                ->options(GradeScale::forFilamentSelect())
                                ->required()
                                ->disabled(fn($record) => $record?->is_locked ?? false)
                                ->visible(fn() => Auth::user()?->hasAnyRole(['super_admin', 'admin', 'principal', 'teacher']) ?? false),

                            Select::make('source')
                                ->label('Marks Source')
                                ->options(MarksSource::forFilamentSelect())
                                ->default(MarksSource::OFFLINE_MANUAL->value)
                                ->required()
                                ->disabled(fn($record) => $record?->is_locked ?? false),

                            Textarea::make('grade_remarks')
                                ->label('Remarks')
                                ->rows(2)
                                ->columnSpanFull()
                                ->placeholder('Optional remarks from the grader...')
                                ->disabled(fn($record) => $record?->is_locked ?? false),
                        ]),
                    ]),

                Section::make('Lock Status')
                    ->schema([
                        Toggle::make('is_locked')
                            ->label('Lock Marks')
                            ->helperText('Once locked, marks cannot be edited except by super_admin. Lock after result publication.')
                            ->visible(fn() => Auth::user()?->hasAnyRole(['super_admin', 'admin', 'principal']) ?? false),
                    ])
                    ->visible(fn($record) => $record !== null), // only show on edit, not create                
            ]);
    }
}
