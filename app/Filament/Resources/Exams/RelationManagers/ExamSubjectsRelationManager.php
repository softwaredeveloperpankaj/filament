<?php

namespace App\Filament\Resources\Exams\RelationManagers;

use App\Models\Subject;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;

class ExamSubjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'subjects';
    protected static ?string $title = 'Exam Subjects';
    protected static ?string $recordTitleAttribute = 'subject.name';
    protected static ?string $permissionPrefix = 'exam_subject';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
                    Select::make('subject_id')
                        ->label('Subject')
                        ->relationship('subject', 'name')
                        ->getOptionLabelFromRecordUsing(
                            fn ($record) => "{$record->name} ({$record->code})"
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->disableOptionWhen(fn($value) => $this->getOwnerRecord()
                            ->subjects()
                            ->where('subject_id', $value)
                            ->exists()),

                    TextInput::make('max_marks')
                        ->label('Max Marks')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(1000)
                        ->default(100)
                        ->required(),

                    TextInput::make('pass_marks')
                        ->label('Pass Marks')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(fn($get) => $get('max_marks') ?? 100)
                        ->default(fn($get) => round(($get('max_marks') ?? 100) * 0.33))
                        ->required(),

                    TextInput::make('theory_marks')
                        ->label('Theory Marks')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(fn($get) => $get('max_marks') ?? 100)
                        ->default(fn($get) => round(($get('max_marks') ?? 100) * 0.8))
                        ->required(),

                    TextInput::make('practical_marks')
                        ->label('Practical Marks')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(fn($get) => $get('max_marks') ?? 100)
                        ->default(fn($get) => round(($get('max_marks') ?? 100) * 0.2))
                        ->required(),

                    TextInput::make('duration_minutes')
                        ->label('Duration (minutes)')
                        ->numeric()
                        ->minValue(15)
                        ->maxValue(480)
                        ->default(180)
                        ->required(),

                    KeyValue::make('paper_structure')
                        ->label('Paper Structure')
                        ->keyLabel('Question Type')
                        ->valueLabel('Marks')
                        ->addActionLabel('Add Section')
                        ->default([
                            'mcq' => 20,
                            'short_answer' => 30,
                            'long_answer' => 50,
                        ])
                        ->columnSpanFull(),

                    TextInput::make('display_order')
                        ->label('Display Order')
                        ->numeric()
                        ->default(0),

                    Toggle::make('is_graded')
                        ->label('Graded Subject')
                        ->default(true)
                        ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('subject.name')
            ->columns([
                TextColumn::make('subject.name')
                    ->label('Subject')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('max_marks')
                    ->label('Max Marks')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('pass_marks')
                    ->label('Pass Marks')
                    ->badge()
                    ->color('success'),

                TextColumn::make('theory_marks')
                    ->label('Theory')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('practical_marks')
                    ->label('Practical')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->suffix(' min')
                    ->badge(),

                IconColumn::make('is_graded')
                    ->label('Graded')
                    ->boolean(),

                TextColumn::make('display_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->defaultSort('display_order')
            ->headerActions([
                CreateAction::make()->label('Add Subject'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}