<?php

namespace App\Filament\Resources\ExamQuestionPapers\Schemas;

use App\Models\ExamQuestionPaper;
use App\Models\QuestionBankItem;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExamQuestionPaperInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | Paper Overview
                |--------------------------------------------------------------------------
                */
                Section::make('Paper Overview')
                    ->description('Basic information about this examination paper.')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                            'xl' => 4,
                        ])->schema([

                            TextEntry::make('exam.name')
                                ->label('Exam')
                                ->icon('heroicon-o-academic-cap')
                                ->weight('bold')
                                ->color('primary'),

                            TextEntry::make('examSubject.subject.name')
                                ->label('Subject')
                                ->icon('heroicon-o-book-open')
                                ->weight('bold'),

                            TextEntry::make('questionBank.name')
                                ->label('Question Bank')
                                ->icon('heroicon-o-archive-box')
                                ->badge()
                                ->color('info')
                                ->placeholder('—'),

                            TextEntry::make('total_marks')
                                ->label('Total Marks')
                                ->icon('heroicon-o-calculator')
                                ->badge()
                                ->color('success')
                                ->suffix(' Marks'),
                        ]),
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Question Summary
                |--------------------------------------------------------------------------
                */
                Section::make('Question Summary')
                    ->description('Question selection and paper configuration.')
                    ->icon('heroicon-o-queue-list')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 3,
                        ])->schema([

                            TextEntry::make('selected_questions')
                                ->label('Total Questions')
                                ->icon('heroicon-o-list-bullet')
                                ->state(function (ExamQuestionPaper $record): int {
                                    return count($record->selected_questions ?? []);
                                })
                                ->badge()
                                ->color('primary')
                                ->suffix(' Questions'),

                            IconEntry::make('is_shuffled')
                                ->label('Question Order')
                                ->boolean()
                                ->trueIcon('heroicon-o-arrow-path')
                                ->falseIcon('heroicon-o-bars-3')
                                ->trueColor('info')
                                ->falseColor('gray'),

                            TextEntry::make('average_marks')
                                ->label('Average Marks / Question')
                                ->state(function (ExamQuestionPaper $record): string {
                                    $questions = $record->selected_questions ?? [];

                                    if (empty($questions)) {
                                        return '0';
                                    }

                                    return number_format(
                                        collect($questions)->values()->avg(),
                                        2
                                    );
                                })
                                ->icon('heroicon-o-chart-bar')
                                ->badge()
                                ->color('warning')
                                ->suffix(' Marks'),
                        ]),
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Assigned Questions
                |--------------------------------------------------------------------------
                */
                Section::make('Assigned Questions')
                    ->description('Questions included in this paper and their assigned marks.')
                    ->schema([
                        RepeatableEntry::make('question_rows')
                            ->table([
                                TableColumn::make('Question #'),
                                TableColumn::make('Question'),
                                TableColumn::make('Marks'),
                            ])
                            ->state(function (ExamQuestionPaper $record): array {
                                $selected = $record->selected_questions ?? [];

                                if (empty($selected)) {
                                    return [];
                                }

                                $questions = QuestionBankItem::query()
                                    ->whereIn('id', array_keys($selected))
                                    ->get()
                                    ->keyBy('id');

                                return collect($selected)
                                    ->map(function ($marks, $questionId) use ($questions): array {
                                        $question = $questions->get($questionId);

                                        return [
                                            'question_no' => $questionId,
                                            'question' => $question?->question_text
                                                ?? 'Question not found',
                                            'marks' => $marks,
                                        ];
                                    })
                                    ->values()
                                    ->toArray();
                            })
                            ->schema([
                                TextEntry::make('question_no')
                                    ->badge()
                                    ->color('gray'),

                                TextEntry::make('question')
                                    ->wrap(),

                                TextEntry::make('marks')
                                    ->badge()
                                    ->color('success')
                                    ->suffix(' Mark(s)'),
                            ]),
                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | Paper Sections
                |--------------------------------------------------------------------------
                */
                Section::make('Paper Sections')
                    ->description('Sections configured for this examination paper.')
                    ->schema([
                        RepeatableEntry::make('section_rows')
                            ->label('')
                            ->table([
                                TableColumn::make('Section'),
                                TableColumn::make('Description / Marks'),
                            ])
                            ->state(function (ExamQuestionPaper $record): array {
                                return collect($record->sections ?? [])
                                    ->map(
                                        fn ($description, $section): array => [
                                            'section' => $section,
                                            'description' => $description,
                                        ]
                                    )
                                    ->values()
                                    ->toArray();
                            })
                            ->schema([
                                TextEntry::make('section')
                                    ->badge()
                                    ->color('primary')
                                    ->weight('bold'),

                                TextEntry::make('description')
                                    ->wrap(),
                            ]),
                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | Instructions
                |--------------------------------------------------------------------------
                */
                Section::make('Paper Instructions')
                    ->description('Instructions displayed on the examination paper.')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextEntry::make('instructions')
                            ->label('')
                            ->placeholder('No instructions have been added.')
                            ->prose()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                /*
                |--------------------------------------------------------------------------
                | Generation Details
                |--------------------------------------------------------------------------
                */
                Section::make('Generation Details')
                    ->description('Information about when and by whom this paper was created.')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                            'xl' => 4,
                        ])->schema([

                            TextEntry::make('generator.name')
                                ->label('Generated By')
                                ->icon('heroicon-o-user')
                                ->weight('medium')
                                ->placeholder('System'),

                            TextEntry::make('created_at')
                                ->label('Created')
                                ->icon('heroicon-o-calendar')
                                ->dateTime('d M Y, h:i A')
                                ->placeholder('—'),

                            TextEntry::make('updated_at')
                                ->label('Last Updated')
                                ->icon('heroicon-o-arrow-path')
                                ->dateTime('d M Y, h:i A')
                                ->placeholder('—'),

                            TextEntry::make('id')
                                ->label('Paper ID')
                                ->icon('heroicon-o-hashtag')
                                ->badge()
                                ->color('gray'),
                        ]),
                    ])
                    ->collapsed(),
            ]);
    }
}