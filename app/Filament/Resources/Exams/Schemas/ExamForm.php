<?php

namespace App\Filament\Resources\Exams\Schemas;

use App\Enums\ExamMode;
use App\Enums\ExamStatus;
use App\Models\AcademicYear;
use App\Models\BranchClass;
use App\Models\ClassSection;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ExamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                        // ── Branch & Class ──
                        Select::make('branch_id')
                            ->label('Branch')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn(Set $set) => $set('branch_class_id', null))
                            // ->visible(fn() => Auth::user()?->hasRole('super_admin') ?? false)
                            ->default(fn() => Auth::user()?->branch_id),

                        Select::make('branch_class_id')
                            ->label('Class')
                            ->options(fn(Get $get) => BranchClass::query()
                                ->where('branch_id', $get('branch_id') ?? Auth::user()?->branch_id)
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn(Set $set) => $set('section_id', null)),

                        // ── Section & Academic Year ──
                        Select::make('section_id')
                            ->label('Section')
                            ->options(
                                fn($get) =>
                                $get('branch_class_id')
                                    ? ClassSection::where('branch_class_id', $get('branch_class_id'))
                                    ->with('section')
                                    ->get()
                                    ->pluck('section.name', 'section_id')
                                    : []
                            )
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
                            ->afterStateUpdated(fn(?string $state, Set $set) => $set('slug', Str::slug($state) . '-' . now()->format('YmdHis'))),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(120)
                            ->unique(ignoreRecord: true),

                        // ── Mode & Status ──
                        Select::make('mode')
                            ->label('Exam Mode')
                            ->options(ExamMode::forFilamentSelect())
                            ->required()
                            ->default(ExamMode::OFFLINE)
                            ->live()
                            ->afterStateUpdated(fn($state, Set $set) => $set('settings', [
                                'shuffle_questions' => false,
                                'negative_marking' => 0,
                                'show_result_immediately' => false,
                                'available_from' => null,
                                'available_until' => null,
                            ])),

                        Select::make('status')
                            ->label('Status')
                            ->options(ExamStatus::forFilamentSelect())
                            ->default(ExamStatus::DRAFT)
                            ->required()
                            ->disabled(fn($record) => $record && $record->status !== ExamStatus::DRAFT->value),

                        // ── Dates & Times ──
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->minDate(fn(Get $get) => $get('academic_year_id') ? AcademicYear::find($get('academic_year_id'))?->start_date : null)
                            ->maxDate(fn(Get $get) => $get('academic_year_id') ? AcademicYear::find($get('academic_year_id'))?->end_date : null),

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
                            ->visible(fn (Get $get) => in_array($get('mode'), [ExamMode::ONLINE, ExamMode::ONLINE?->value]))                            
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
                            ->default(Auth::id()),

            ]);
    }
}
