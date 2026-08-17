<?php

namespace App\Filament\Resources\ExamQuestionPapers\Schemas;

use App\Models\ExamSubject;
use App\Models\QuestionBank;
use App\Models\QuestionBankItem;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ExamQuestionPaperForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Paper Configuration')
                ->description('Link this paper to an exam subject and select a question bank to draw questions from.')
                ->schema([
                    Grid::make(2)->schema([

                        // Exam (scoped to user's branch)
                        Select::make('exam_id')
                            ->label('Exam')
                            ->relationship('exam', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set) => $set('exam_subject_id', null))
                            ->disabled(fn($record) => $record !== null),

                        // Exam Subject (scoped to selected exam, online mode only makes sense here)
                        Select::make('exam_subject_id')
                            ->label('Exam Subject')
                            ->options(fn(Get $get) => $get('exam_id')
                                ? ExamSubject::query()
                                    ->where('exam_id', $get('exam_id'))
                                    ->with('subject')
                                    ->get()
                                    ->pluck('subject.name', 'id')
                                : [])
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->disabled(fn($record) => $record !== null)
                            ->afterStateUpdated(fn(callable $set) => $set('question_bank_id', null)),

                        // Question Bank (scoped to exam subject's subject + branch)
                        Select::make('question_bank_id')
                            ->label('Question Bank')
                            ->options(function (Get $get) {
                                $examSubject = ExamSubject::find($get('exam_subject_id'));
                                if (!$examSubject) {
                                    return [];
                                }
                                return QuestionBank::query()
                                    ->where('branch_id', $examSubject->exam->branch_id)
                                    ->where('subject_id', $examSubject->subject_id)
                                    ->where('is_active', true)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->helperText('Only active banks matching this subject and branch are shown.'),

                        // Shuffle Questions
                        Toggle::make('is_shuffled')
                            ->label('Shuffle Questions')
                            ->default(false)
                            ->helperText('Randomize question order for each student attempt.'),
                    ]),
                ]),

            Section::make('Select Questions')
                ->description('Pick questions from the bank and assign marks per question.')
                ->schema([
                    Select::make('selected_question_ids')
                        ->label('Questions')
                        ->multiple()
                        ->options(fn(Get $get) => $get('question_bank_id')
                            ? QuestionBankItem::query()
                                ->where('question_bank_id', $get('question_bank_id'))
                                ->where('is_active', true)
                                ->get()
                                ->mapWithKeys(fn($q) => [$q->id => str($q->question_text)->limit(80) . " ({$q->marks} marks)"])
                            : [])
                        ->searchable()
                        ->preload()
                        ->required()
                        ->reactive()
                        ->columnSpanFull()
                        ->helperText('Select at least 5 questions to generate a valid paper.')
                        ->dehydrated(false) // not saved directly, transformed into selected_questions JSON below
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (is_array($state)) {
                                $marksMap = QuestionBankItem::whereIn('id', $state)
                                    ->pluck('marks', 'id')
                                    ->toArray();
                                $set('selected_questions', $marksMap);
                            }
                        }),

                    KeyValue::make('selected_questions')
                        ->label('Assigned Marks per Question')
                        ->keyLabel('Question ID')
                        ->valueLabel('Marks')
                        ->columnSpanFull()
                        ->helperText('Auto-populated from selection above. Adjust marks per question if needed.'),
                ]),

            Section::make('Paper Sections & Instructions')
                ->schema([
                    KeyValue::make('sections')
                        ->label('Paper Sections')
                        ->keyLabel('Section (A, B, C...)')
                        ->valueLabel('Description / Marks')
                        ->addActionLabel('Add Section')
                        ->columnSpanFull()
                        ->default([
                            'A' => 'MCQ - 20 Marks',
                            'B' => 'Short Answer - 30 Marks',
                            'C' => 'Long Answer - 50 Marks',
                        ]),

                    Textarea::make('instructions')
                        ->label('Paper Instructions')
                        ->rows(3)
                        ->columnSpanFull()
                        ->placeholder('General instructions printed at the top of the paper...'),

                    Hidden::make('generated_by')
                        ->default(Auth::id()),

                    Hidden::make('total_marks')
                        ->default(0), // recalculated in model boot via selected_questions sum
                ]),
        ]);        
    }
}
