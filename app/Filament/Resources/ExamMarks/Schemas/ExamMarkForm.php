<?php

namespace App\Filament\Resources\ExamMarks\Schemas;

use App\Enums\GradeScale;
use App\Enums\MarksSource;
use App\Models\ExamMark;
use App\Models\ExamSubject;
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
                Section::make('Exam Marks Information')
                    ->description('Manage student examination marks, academic references, and grading information.')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([

                        // Reference Information
                        Section::make('Reference Details')
                            ->description('Select the examination, subject, and student for marks entry.')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 3,
                                ])->schema([

                                    Select::make('exam_id')
                                        ->label('Exam')
                                        ->relationship('exam', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->disabled(fn ($record) => $record !== null)
                                        ->afterStateUpdated(
                                            fn (callable $set) => $set('exam_subject_id', null)
                                        ),

                                    Select::make('exam_subject_id')
                                        ->label('Subject')
                                        ->options(fn (Get $get) => $get('exam_id')
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
                                        ->disabled(fn ($record) => $record !== null)
                                        ->afterStateUpdated(
                                            fn (callable $set) => $set('exam_student_entry_id', null)
                                        ),

                                    Select::make('exam_student_entry_id')
                                        ->label('Student')
                                        ->options(fn (Get $get) => $get('exam_id')
                                            ? \App\Models\ExamStudentEntry::query()
                                                ->where('exam_id', $get('exam_id'))
                                                ->with('student')
                                                ->get()
                                                ->mapWithKeys(fn ($entry) => [
                                                    $entry->id => "{$entry->student->name} ({$entry->roll_no})",
                                                ])
                                            : [])
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->disabled(fn ($record) => $record !== null)
                                        ->hint(function (Get $get, $record) {
                                            if ($record) {
                                                return null;
                                            }

                                            $entryId = $get('exam_student_entry_id');
                                            $subjectId = $get('exam_subject_id');

                                            if (! $entryId || ! $subjectId) {
                                                return null;
                                            }

                                            $exists = ExamMark::query()
                                                ->where('exam_student_entry_id', $entryId)
                                                ->where('exam_subject_id', $subjectId)
                                                ->exists();

                                            return $exists
                                                ? 'Marks already exist for this student in this subject.'
                                                : null;
                                        })
                                        ->hintColor('danger'),

                                ]),
                            ])
                            ->collapsible()
                            ->columnSpanFull(),

                        // Marks Entry
                        Section::make('Marks Entry')
                            ->description(fn ($record) => $record?->is_locked
                                ? 'These marks are locked and cannot be edited.'
                                : 'Enter theory, practical, and internal assessment marks.')
                            ->icon('heroicon-o-pencil-square')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 3,
                                ])->schema([

                                    TextInput::make('theory_obtained')
                                        ->label('Theory Obtained')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(fn (Get $get) => $get('theory_maximum') ?? 100)
                                        ->default(0)
                                        ->required()
                                        ->disabled(fn ($record) => $record?->is_locked ?? false),

                                    TextInput::make('theory_maximum')
                                        ->label('Theory Maximum')
                                        ->numeric()
                                        ->minValue(0)
                                        ->default(fn (Get $get) =>
                                            ExamSubject::find($get('exam_subject_id'))?->theory_marks ?? 0
                                        )
                                        ->required()
                                        ->disabled(fn ($record) => $record?->is_locked ?? false),

                                    TextInput::make('practical_obtained')
                                        ->label('Practical Obtained')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(fn (Get $get) => $get('practical_maximum') ?? 100)
                                        ->default(0)
                                        ->disabled(fn ($record) => $record?->is_locked ?? false),

                                    TextInput::make('practical_maximum')
                                        ->label('Practical Maximum')
                                        ->numeric()
                                        ->minValue(0)
                                        ->default(fn (Get $get) =>
                                            ExamSubject::find($get('exam_subject_id'))?->practical_marks ?? 0
                                        )
                                        ->disabled(fn ($record) => $record?->is_locked ?? false),

                                    TextInput::make('internal_obtained')
                                        ->label('Internal / Assignment Obtained')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(fn (Get $get) => $get('internal_maximum') ?? 100)
                                        ->default(0)
                                        ->disabled(fn ($record) => $record?->is_locked ?? false),

                                    TextInput::make('internal_maximum')
                                        ->label('Internal / Assignment Maximum')
                                        ->numeric()
                                        ->minValue(0)
                                        ->default(0)
                                        ->disabled(fn ($record) => $record?->is_locked ?? false),

                                ]),
                            ])
                            ->collapsible()
                            ->columnSpanFull(),

                        // Status and Notes
                        Section::make('Status & Notes')
                            ->description('Manage student attendance, exemption, marks source, and teacher remarks.')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->schema([

                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 3,
                                ])->schema([

                                    Toggle::make('absent')
                                        ->label('Absent')
                                        ->helperText('Zeroes obtained marks and marks the student as failed for this subject.')
                                        ->disabled(fn ($record) => $record?->is_locked ?? false),

                                    Toggle::make('exempted')
                                        ->label('Exempted')
                                        ->helperText('Excludes this subject from the student pass status.')
                                        ->disabled(fn ($record) => $record?->is_locked ?? false),

                                    Select::make('source')
                                        ->label('Marks Source')
                                        ->options(MarksSource::forFilamentSelect())
                                        ->default(MarksSource::OFFLINE_MANUAL->value)
                                        ->required()
                                        ->disabled(fn ($record) => $record?->is_locked ?? false),

                                ]),

                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                ])->schema([

                                    Select::make('grade')
                                        ->label('Grade')
                                        ->options(GradeScale::forFilamentSelect())
                                        ->required()
                                        ->visible(fn () => Auth::user()?->hasAnyRole([
                                            'super_admin',
                                            'admin',
                                            'principal',
                                            'teacher',
                                        ]) ?? false)
                                        ->disabled(fn ($record) => $record?->is_locked ?? false),

                                    Textarea::make('remarks')
                                        ->label('Teacher Remarks')
                                        ->rows(3)
                                        ->placeholder('Optional remarks from the grader...')
                                        ->disabled(fn ($record) => $record?->is_locked ?? false)
                                        ->columnSpanFull(),

                                ]),
                            ])
                            ->collapsible()
                            ->columnSpanFull(),

                        // Lock Status
                        Section::make('Lock Status')
                            ->description('Control whether examination marks can be modified.')
                            ->icon('heroicon-o-lock-closed')
                            ->schema([
                                Toggle::make('is_locked')
                                    ->label('Lock Marks')
                                    ->helperText('Once locked, marks cannot be edited except by authorized super administrators. Lock after result publication.')
                                    ->visible(fn () => Auth::user()?->hasAnyRole([
                                        'super_admin',
                                        'admin',
                                        'principal',
                                    ]) ?? false),
                            ])
                            ->visible(fn ($record) => $record !== null)
                            ->collapsible()
                            ->columnSpanFull(),

                    ])
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}
