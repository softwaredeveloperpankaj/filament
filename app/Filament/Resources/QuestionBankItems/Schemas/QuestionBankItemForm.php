<?php

namespace App\Filament\Resources\QuestionBankItems\Schemas;

use App\Enums\DifficultyLevel;
use App\Enums\QuestionType;
use App\Models\QuestionBank;
use App\Models\Topic;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class QuestionBankItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(1)->schema([
                Section::make('Question Location')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('question_bank_id')
                                ->label('Question Bank')
                                ->relationship('questionBank', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn($state, callable $set) => $set(
                                    'subject_id',
                                    QuestionBank::find($state)?->subject_id
                                )),
    
                            Select::make('subject_id')
                                ->label('Subject')
                                ->relationship('subject', 'name')
                                ->disabled()
                                ->dehydrated()
                                ->live()
                                ->required(),
    
                        ]),
                    ]),
    
                Section::make('Question Details')
                    ->schema([
                        Grid::make(2)->schema([
    
                            Select::make('question_type')
                                ->label('Question Type')
                                ->options(QuestionType::forFilamentSelect())
                                ->required()
                                ->reactive()
                                ->default(QuestionType::MCQ->value),
    
                            Select::make('topic_id')
                                ->label('Topic / Chapter')
                                ->relationship(
                                    name: 'topic', 
                                    titleAttribute: 'name', 
                                    modifyQueryUsing: fn (Builder $query, Get $get) => $query->where('subject_id', $get('subject_id'))
                                )
                                ->searchable()
                                ->preload()
                                ->live()
                                ->disabled(fn (Get $get) => ! $get('subject_id'))
                                ->placeholder(fn (Get $get) => $get('subject_id') ? 'Select topic/chapter' : 'Select Question Bank first'),
    
                            Select::make('difficulty')
                                ->label('Difficulty')
                                ->options(DifficultyLevel::forFilamentSelect())
                                ->required()
                                ->default(DifficultyLevel::MEDIUM->value),
    
                            TextInput::make('marks')
                                ->label('Marks')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(100)
                                ->default(1)
                                ->required(),
    
                            TextInput::make('negative_marks')
                                ->label('Negative Marks')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(fn(Get $get) => $get('marks') ?? 1)
                                ->default(0)
                                ->step(0.25),
    
                            TextInput::make('tags')
                                ->label('Tags (comma-separated)')
                                ->placeholder('algebra, chapter-1, quadratic-equations'),
    
                            Textarea::make('question_text')
                                ->label('Question Text')
                                ->required()
                                ->rows(3)
                                ->columnSpanFull(),
    
                            KeyValue::make('options')
                                ->label('Options')
                                ->keyLabel('Option Key')
                                ->valueLabel('Option Text')
                                ->addActionLabel('Add Option')
                                ->reorderable()
                                ->visible(fn(Get $get) => in_array($get('question_type'), [
                                    QuestionType::MCQ->value,
                                    QuestionType::TRUE_FALSE->value,
                                    QuestionType::MATCHING->value,
                                ]))
                                ->columnSpanFull()
                                ->default(['A' => '', 'B' => '', 'C' => '', 'D' => '']),
    
                            KeyValue::make('correct_answer')
                                ->label('Correct Answer(s)')
                                ->keyLabel('Question Part')
                                ->valueLabel('Answer')
                                ->addActionLabel('Add Answer')
                                ->required()
                                ->columnSpanFull(),
    
                            Textarea::make('explanation')
                                ->label('Explanation / Solution')
                                ->rows(3)
                                ->columnSpanFull(),
    
                            FileUpload::make('media')
                                ->label('Attach Media (Image/Diagram)')
                                ->image()
                                ->maxSize(2048)
                                ->directory('question-banks')
                                ->columnSpanFull(),
    
                            Toggle::make('is_active')
                                ->label('Active')
                                ->default(true)
                                ->columnSpanFull(),
    
    
                            Hidden::make('created_by')
                                ->default(Auth::id()),
                        ]),
                    ]),
            ])->columnSpanFull()
        ]);
    }
}
