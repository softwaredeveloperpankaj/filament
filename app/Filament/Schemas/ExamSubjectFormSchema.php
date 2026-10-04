<?php

namespace App\Filament\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class ExamSubjectFormSchema
{
    public static function getComponents(): array
    {
        return [
            Section::make('Subject Configuration')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('subject_id')
                            ->relationship('subject', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        TextInput::make('duration_minutes')
                            ->numeric()
                            ->default(180)
                            ->required(),
                    ]),
                    Grid::make(4)->schema([
                        TextInput::make('max_marks')->numeric()->default(100)->required(),
                        TextInput::make('pass_marks')->numeric()->default(33)->required(),
                        TextInput::make('theory_marks')->numeric()->default(80)->required(),
                        TextInput::make('practical_marks')->numeric()->default(20)->required(),
                    ]),
                    Toggle::make('is_graded')->default(true),
                ]),
        ];
    }
}