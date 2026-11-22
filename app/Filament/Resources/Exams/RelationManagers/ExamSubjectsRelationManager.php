<?php

namespace App\Filament\Resources\Exams\RelationManagers;

use App\Models\BranchClass;
use App\Models\ClassSection;
use App\Models\Subject;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExamSubjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'subjects';
    protected static ?string $title = 'Exam Subjects';
    protected static ?string $recordTitleAttribute = 'subject.name';
    protected static ?string $permissionPrefix = 'exam_subject';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
                    Select::make('branch_class_id')
                        ->label('Class')
                        ->options(fn () => BranchClass::query()
                            ->where('branch_id', $this->getOwnerRecord()?->branch_id)
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->default(fn () => $this->getOwnerRecord()?->branch_class_id)
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn (Set $set) => $set('section_id', null)),

                    Select::make('section_id')
                        ->label('Section')
                        ->options(fn (Get $get) => $get('branch_class_id')
                            ? ClassSection::query()
                                ->where('branch_class_id', $get('branch_class_id'))
                                ->with('section')
                                ->get()
                                ->pluck('section.name', 'section_id')
                            : [])
                        ->searchable()
                        ->preload()
                        ->default(fn () => $this->getOwnerRecord()?->section_id)
                        ->required(),

                    Select::make('subject_id')
                        ->label('Subject')
                        ->options(function (Get $get) {
                            $branchClassId = $get('branch_class_id');
                            $sectionId = $get('section_id');

                            return Subject::query()
                                ->when($branchClassId, fn ($query) => $query->whereHas('sectionSubjects', function ($sectionSubjectQuery) use ($branchClassId, $sectionId) {
                                    $sectionSubjectQuery->where('branch_class_id', $branchClassId)
                                        ->when($sectionId, fn ($query) => $query->where('section_id', $sectionId));
                                }))
                                ->where('branch_id', $this->getOwnerRecord()?->branch_id)
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn ($subject) => [$subject->id => "{$subject->name} ({$subject->code})"])
                                ->all();
                        })
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
                    ->getStateUsing(fn ($record) => $record->subject ? $record->subject->name . ' (' . $record->subject->code . ')' : null)
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
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->recordActionsColumnLabel('Actions')
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}