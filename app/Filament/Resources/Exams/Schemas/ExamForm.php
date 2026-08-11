<?php

namespace App\Filament\Resources\Exams\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
            Section::make('Exam Information')
                ->schema([
                    Grid::make(2)->schema([

                        // ── Branch & Class ──
                        Select::make('branch_id')
                            ->label('Branch')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn(HasSchemaComponents $set) => $set('branch_class_id', null))
                            ->visible(fn() => auth()->user()?->hasRole('super_admin') ?? false)
                            ->default(fn() => auth()->user()?->branch_id),

                        Select::make('branch_class_id')
                            ->label('Class')
                            ->options(fn(HasSchemaComponents $get) => BranchClass::query()
                                ->where('branch_id', $get('branch_id') ?? auth()->user()?->branch_id)
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn(HasSchemaComponents $set) => $set('section_id', null)),

                        // ── Section & Academic Year ──
                        Select::make('section_id')
                            ->label('Section')
                            ->options(fn(HasSchemaComponents $get) => Section::query()
                                ->where('branch_class_id', $get('branch_class_id'))
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('academic_year_id')
                            ->label('Academic Year')
                            ->relationship('academicYear', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn() => AcademicYear::current()?->id),

                        // ── Basic Info ──
                        TextInput::make('name')
                            ->label('Exam Name')
                            ->placeholder('e.g. Mid Term, Final Term, Unit Test 1')
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(?string $state, HasSchemaComponents $set) => $set('slug', \Str::slug($state) . '-' . now()->format('YmdHis'))),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(120)
                            ->unique(ignoreRecord: true),

                        // ── Mode & Status ──
                        Select::make('mode')
                            ->label('Exam Mode')
                            ->options(ExamMode::class)
                            ->required()
                            ->default(ExamMode::OFFLINE)
                            ->reactive()
                            ->afterStateUpdated(fn($state, HasSchemaComponents $set) => $set('settings', [
                                'shuffle_questions' => false,
                                'negative_marking' => 0,
                                'show_result_immediately' => false,
                                'available_from' => null,
                                'available_until' => null,
                            ])),

                        Select::make('status')
                            ->label('Status')
                            ->options(ExamStatus::class)
                            ->default(ExamStatus::DRAFT)
                            ->required()
                            ->disabled(fn($record) => $record && $record->status !== ExamStatus::DRAFT->value),

                        // ── Dates & Times ──
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->minDate(fn(HasSchemaComponents $get) => $get('academic_year_id') ? AcademicYear::find($get('academic_year_id'))?->start_date : null)
                            ->maxDate(fn(HasSchemaComponents $get) => $get('academic_year_id') ? AcademicYear::find($get('academic_year_id'))?->end_date : null),

                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->required()
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->after('start_date'),

                        TimePicker::make('start_time')
                            ->label('Daily Start Time')
                            ->seconds(false)
                            ->default('09:00'),

                        TimePicker::make('end_time')
                            ->label('Daily End Time')
                            ->seconds(false)
                            ->default('17:00'),

                        // ── Online Settings (conditional) ──
                        KeyValue::make('settings')
                            ->label('Online Exam Settings')
                            ->visible(fn(HasSchemaComponents $get) => $get('mode') === ExamMode::ONLINE->value)
                            ->keyLabel('Setting')
                            ->valueLabel('Value')
                            ->addActionLabel('Add Setting')
                            ->default([
                                'shuffle_questions' => false,
                                'negative_marking' => 0,
                                'show_result_immediately' => false,
                                'available_from' => null,
                                'available_until' => null,
                            ])
                            ->columnSpanFull(),

                        // ── Other ──
                        Toggle::make('is_practical')
                            ->label('Has Practical Component')
                            ->default(false),

                        Textarea::make('instructions')
                            ->label('Instructions for Students')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('General instructions printed on admit card and question paper...'),

                        Hidden::make('created_by')
                            ->default(auth()->id()),
                    ]),
                ]),
            ]);
    }
}
