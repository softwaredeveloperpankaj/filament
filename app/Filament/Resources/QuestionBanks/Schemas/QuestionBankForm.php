<?php

namespace App\Filament\Resources\QuestionBanks\Schemas;

use App\Models\Subject;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class QuestionBankForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('branch_id')
                    ->label('Branch')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn($state, callable $set) => $set('subject_id', null)),

                Select::make('subject_id')
                    ->relationship('subject', 'name')
                    ->getOptionLabelFromRecordUsing(
                        fn ($record) => "{$record->name} ({$record->code})"
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(fn(Get $get) => !$get('branch_id')),

                TextInput::make('name')
                    ->label('Bank Name')
                    ->placeholder('e.g. Mathematics - Chapter 1-5, Physics Term 1')
                    ->required()
                    ->maxLength(150)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),

                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(160)
                    ->unique(ignoreRecord: true)
                    ->helperText('Auto-generated from name. Used in URLs.'),

                Select::make('type')
                    ->label('Question Type Mix')
                    ->options([
                        'objective' => 'Objective Only (MCQ, True/False, Fill Blank)',
                        'subjective' => 'Subjective Only (Short/Long Answer)',
                        'mixed' => 'Mixed (Both Objective & Subjective)',
                    ])
                    ->required()
                    ->default('mixed')
                    ->helperText('Determines which question types are allowed in this bank.'),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->columnSpanFull()
                    ->placeholder('What topics/chapters does this bank cover? Any special instructions for teachers?'),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Inactive banks cannot be used to generate exam papers.'),

                Hidden::make('created_by')
                    ->default(Auth::id()),

            ]);


    }
}
