<?php

namespace App\Filament\Resources\ExamSubjects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ExamSubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('exam_id')
                    ->relationship('exam', 'name')
                    ->required(),
                Select::make('subject_id')
                    ->relationship('subject', 'name')
                    ->required(),
                TextInput::make('max_marks')
                    ->required()
                    ->numeric()
                    ->default(100),
                TextInput::make('pass_marks')
                    ->required()
                    ->numeric()
                    ->default(33),
                TextInput::make('theory_marks')
                    ->required()
                    ->numeric()
                    ->default(80),
                TextInput::make('practical_marks')
                    ->required()
                    ->numeric()
                    ->default(20),
                TextInput::make('duration_minutes')
                    ->required()
                    ->numeric()
                    ->default(180),
                TextInput::make('paper_structure')
                    ->label('Paper Structure (JSON)')
                    ->json()
                    ->nullable()
                    ->placeholder('{ "mcq": 20, "short": 30, "long": 50 }')
                    ->helperText('e.g. { "mcq": 20, "short": 30, "long": 50 }'),
                TextInput::make('display_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_graded')
                    ->label('Is Graded')
                    ->default(false)
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
