<?php

namespace App\Filament\Schemas;

use App\Enums\DifficultyLevel;
use App\Enums\QuestionType;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class QuestionBankItemFormSchema
{
    public static function getComponents(): array
    {
        return [
            Section::make('Question Details')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('question_type')
                            ->options(QuestionType::class)
                            ->required(),

                        Select::make('difficulty')
                            ->options(DifficultyLevel::class)
                            ->default(DifficultyLevel::MEDIUM)
                            ->required(),

                        TextInput::make('marks')
                            ->numeric()
                            ->default(1)
                            ->required(),
                    ]),
                    RichEditor::make('question_text')
                        ->required(),
                    KeyValue::make('options')
                        ->label('Options (for MCQ/Matching)')
                        ->keyLabel('Option Key (e.g. A, B, C)')
                        ->valueLabel('Option Text'),
                    Textarea::make('correct_answer')
                        ->label('Correct Answer(s)')
                        ->helperText('String or JSON formatted answer key'),
                    Textarea::make('explanation')
                        ->rows(2),
                    Toggle::make('is_active')->default(true),
                ]),
        ];
    }
}