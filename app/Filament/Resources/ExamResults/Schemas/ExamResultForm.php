<?php

namespace App\Filament\Resources\ExamResults\Schemas;

use App\Enums\GradeScale;
use App\Enums\ResultStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ExamResultForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Select::make('exam_id')
                //     ->relationship('exam', 'name')
                //     ->required(),
                // Select::make('student_id')
                //     ->relationship('student', 'id')
                //     ->required(),
                // TextInput::make('exam_student_entry_id')
                //     ->required()
                //     ->numeric(),
                // TextInput::make('grand_total_obtained')
                //     ->required()
                //     ->numeric()
                //     ->default(0),
                // TextInput::make('grand_total_maximum')
                //     ->required()
                //     ->numeric()
                //     ->default(0),
                // TextInput::make('overall_percentage')
                //     ->required()
                //     ->numeric()
                //     ->default(0.0),
                // TextInput::make('overall_grade'),
                // TextInput::make('rank_in_class')
                //     ->numeric(),
                // TextInput::make('rank_in_section')
                //     ->numeric(),
                // TextInput::make('rank_in_branch')
                //     ->numeric(),
                // Toggle::make('is_passed')
                //     ->required(),
                // TextInput::make('failed_subjects'),
                // Select::make('status')
                //     ->options(ResultStatus::class)
                //     ->default('draft')
                //     ->required(),
                // TextInput::make('published_by')
                //     ->numeric(),
                // DateTimePicker::make('published_at'),
                // TextInput::make('subject_wise_breakdown'),

                Section::make('Result Info (Read-Only)')
                    ->description('Totals, percentage, and rank are calculated automatically from exam marks.')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('student.name')
                                ->label('Student')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('exam.name')
                                ->label('Exam')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('grand_total_obtained')
                                ->label('Total Obtained')
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                    ]),

                Section::make('Grade & Rank (Read-Only)')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('overall_grade')
                                ->label('Overall Grade')
                                ->options(GradeScale::forFilamentSelect())
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('rank_in_section')
                                ->label('Section Rank')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('rank_in_class')
                                ->label('Class Rank')
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                    ]),

                Section::make('Publication Status')
                    ->description('Only status can be manually changed — publishing/withholding requires principal or admin approval.')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options(ResultStatus::class)
                            ->required()
                            ->visible(fn() => Auth::user()?->hasAnyRole(['super_admin', 'admin', 'principal']) ?? false)
                            ->helperText('Changing status here bypasses the Publish/Withhold action buttons — use with caution.'),
                    ]),

            ]);
    }
}
