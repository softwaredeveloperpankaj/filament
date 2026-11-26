<?php

namespace App\Filament\Resources\Exams\Schemas;

use App\Enums\ExamMode;
use App\Enums\ExamStatus;
use App\Models\AcademicYear;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ExamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Exam Information')
                ->description('Configure the exam identity, academic assignment, and status.')
                ->icon('heroicon-o-academic-cap')
                ->schema([Grid::make(['default' => 1, 'sm' => 2])->schema([
                    Select::make('branch_id')->label('Branch')->relationship('branch', 'name')->searchable()->preload()->required()->reactive()->default(fn() => Auth::user()?->branch_id),
                    Select::make('academic_year_id')->label('Academic Year')->relationship('academicYear', 'name')->searchable()->preload()->required()->default(fn() => AcademicYear::current()?->id),
                    TextInput::make('name')->label('Exam Name')->placeholder('e.g. Mid Term, Final Term, Unit Test 1')->required()->maxLength(100)->live(onBlur: true)
                        ->afterStateUpdated(fn(?string $state, Set $set) => $set('slug', Str::slug($state) . '-' . now()->format('YmdHis'))),
                    TextInput::make('slug')->label('Slug')->required()->maxLength(120)->unique(ignoreRecord: true),
                    Select::make('mode')->label('Exam Mode')->options(ExamMode::forFilamentSelect())->required()->default(ExamMode::OFFLINE)->live()
                        ->afterStateUpdated(fn($state, Set $set) => $set('settings', ['shuffle_questions' => false, 'negative_marking' => 0, 'show_result_immediately' => false, 'available_from' => null, 'available_until' => null])),
                    Select::make('status')->label('Status')->options(ExamStatus::forFilamentSelect())->default(ExamStatus::DRAFT)->required()
                        ->disabled(fn($record) => $record && $record->status !== ExamStatus::DRAFT->value),
                    Toggle::make('is_practical')->label('Has Practical Component')->default(false),
                ])])->collapsible()->columnSpanFull(),
            Section::make('Schedule')
                ->description('Set the exam date range and daily operating times.')
                ->icon('heroicon-o-calendar-days')
                ->schema([Grid::make(['default' => 1, 'sm' => 2, 'lg' => 4])->schema([
                    DatePicker::make('start_date')->label('Start Date')->required()->native(false)->displayFormat('d M Y')
                        ->minDate(fn(Get $get) => $get('academic_year_id') ? AcademicYear::find($get('academic_year_id'))?->start_date : null)
                        ->maxDate(fn(Get $get) => $get('academic_year_id') ? AcademicYear::find($get('academic_year_id'))?->end_date : null),
                    DatePicker::make('end_date')->label('End Date')->required()->native(false)->displayFormat('d M Y')->after('start_date'),
                    TimePicker::make('start_time')->label('Daily Start Time')->seconds(false)->default('09:00'),
                    TimePicker::make('end_time')->label('Daily End Time')->seconds(false)->default('17:00'),
                ])])->collapsible()->columnSpanFull(),
            Section::make('Online Exam Settings')
                ->description('Configure behavior for online examination modes.')
                ->icon('heroicon-o-computer-desktop')
                ->schema([
                    KeyValue::make('settings')->label('Online Exam Settings')
                        ->visible(fn (Get $get) => in_array($get('mode'), [ExamMode::ONLINE, ExamMode::ONLINE?->value]))
                        ->keyLabel('Setting')->valueLabel('Value')->addActionLabel('Add Setting')
                        ->default(['shuffle_questions' => false, 'negative_marking' => 0, 'show_result_immediately' => false, 'available_from' => null, 'available_until' => null])
                        ->columnSpanFull(),
                ])
                ->visible(fn (Get $get) => in_array($get('mode'), [ExamMode::ONLINE, ExamMode::ONLINE?->value]))
                ->collapsible()->columnSpanFull(),
            Section::make('Instructions')
                ->description('Provide instructions shown on admit cards and question papers.')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Textarea::make('instructions')->label('Instructions for Students')->rows(3)->columnSpanFull()->placeholder('General instructions printed on admit card and question paper...'),
                    Hidden::make('created_by')->default(Auth::id()),
                ])
                ->collapsible()->columnSpanFull(),
        ]);
    }
}
