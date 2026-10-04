<?php

namespace App\Filament\Schemas;

use App\Enums\ExamMode;
use App\Enums\ExamStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class ExamFormSchema
{
    public static function getComponents(): array
    {
        return [
            Section::make('Exam Overview')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Select::make('branch_id')
                            ->relationship('branch', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('academic_year_id')
                            ->relationship('academicYear', 'name')
                            ->required()
                            ->preload(),
                    ]),
                    Grid::make(3)->schema([
                        Select::make('branch_class_id')
                            ->relationship('branchClass', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('section_id')
                            ->relationship('section', 'name')
                            ->nullable()
                            ->searchable()
                            ->preload(),

                        Select::make('mode')
                            ->options(ExamMode::class)
                            ->default(ExamMode::OFFLINE)
                            ->required(),
                    ]),
                    Grid::make(4)->schema([
                        DatePicker::make('start_date')->required(),
                        DatePicker::make('end_date')->required(),
                        TimePicker::make('start_time'),
                        TimePicker::make('end_time'),
                    ]),
                    Grid::make(2)->schema([
                        Select::make('status')
                            ->options(ExamStatus::class)
                            ->default(ExamStatus::DRAFT)
                            ->required(),

                        Toggle::make('is_practical')
                            ->label('Has Practical Component'),
                    ]),
                    Textarea::make('instructions')
                        ->rows(3),
                ]),
        ];
    }
}