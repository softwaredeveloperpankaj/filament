<?php

namespace App\Filament\Resources\ExamResults\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExamResultInfolist
{
    public static function configure(Schema $schema): Schema
    {
        // return $schema
        //     ->components([
        //         TextEntry::make('exam.name')
        //             ->label('Exam'),
        //         TextEntry::make('student.id')
        //             ->label('Student'),
        //         TextEntry::make('exam_student_entry_id')
        //             ->numeric(),
        //         TextEntry::make('grand_total_obtained')
        //             ->numeric(),
        //         TextEntry::make('grand_total_maximum')
        //             ->numeric(),
        //         TextEntry::make('overall_percentage')
        //             ->numeric(),
        //         TextEntry::make('overall_grade')
        //             ->placeholder('-'),
        //         TextEntry::make('rank_in_class')
        //             ->numeric()
        //             ->placeholder('-'),
        //         TextEntry::make('rank_in_section')
        //             ->numeric()
        //             ->placeholder('-'),
        //         TextEntry::make('rank_in_branch')
        //             ->numeric()
        //             ->placeholder('-'),
        //         IconEntry::make('is_passed')
        //             ->boolean(),
        //         TextEntry::make('status')
        //             ->badge(),
        //         TextEntry::make('published_by')
        //             ->numeric()
        //             ->placeholder('-'),
        //         TextEntry::make('published_at')
        //             ->dateTime()
        //             ->placeholder('-'),
        //         TextEntry::make('created_at')
        //             ->dateTime()
        //             ->placeholder('-'),
        //         TextEntry::make('updated_at')
        //             ->dateTime()
        //             ->placeholder('-'),
        //     ]);

        return $schema->components([

            Section::make('Student & Exam')
                ->description('Student and examination reference for this result.')
                ->icon('heroicon-o-user-group')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])->schema([
                        TextEntry::make('student.name')->label('Student'),
                        TextEntry::make('entry.roll_no')->label('Roll No')->badge()->color('gray'),
                        TextEntry::make('exam.name')->label('Exam'),
                    ]),
                ]),

            Section::make('Overall Performance')
                ->description('Scores, percentage, grade, ranks, and pass status.')
                ->icon('heroicon-o-chart-bar')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])->schema([
                        TextEntry::make('grand_total_obtained')
                            ->label('Total Obtained')
                            ->formatStateUsing(fn($state, $record) => "{$state} / {$record->grand_total_maximum}")
                            ->badge()
                            ->weight('bold')
                            ->color(fn($record) => $record->is_passed ? 'success' : 'danger'),

                        TextEntry::make('overall_percentage')
                            ->label('Percentage')
                            ->suffix('%'),

                        TextEntry::make('overall_grade')
                            ->label('Grade')
                            ->badge()
                            ->color(fn($state) => match (true) {
                                in_array($state, ['A+', 'A']) => 'success',
                                in_array($state, ['B+', 'B']) => 'info',
                                in_array($state, ['C+', 'C']) => 'warning',
                                $state === 'D' => 'gray',
                                default => 'danger',
                            }),

                        IconEntry::make('is_passed')
                            ->label('Result')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),

                        TextEntry::make('rank_in_section')
                            ->label('Section Rank')
                            ->badge()
                            ->color('primary')
                            ->placeholder('—'),

                        TextEntry::make('rank_in_class')
                            ->label('Class Rank')
                            ->badge()
                            ->color('info')
                            ->placeholder('—'),
                    ]),
                ]),

            Section::make('Failed Subjects')
                ->description('Subjects that contributed to a failed result.')
                ->icon('heroicon-o-x-circle')
                ->schema([
                    KeyValueEntry::make('failed_subjects')
                        ->label('Subject IDs')
                        ->columnSpanFull()
                        ->placeholder('None — student passed all subjects'),
                ])
                ->visible(fn($record) => !empty($record->failed_subjects))
                ->collapsible(),

            Section::make('Subject-Wise Breakdown')
                ->description('Cached subject-level marks and result details.')
                ->icon('heroicon-o-table-cells')
                ->schema([
                    KeyValueEntry::make('subject_wise_breakdown')
                        ->label('Breakdown')
                        ->columnSpanFull()
                        ->placeholder('Not cached yet'),
                ])
                ->collapsible()
                ->collapsed(),

            Section::make('Publication')
                ->description('Publication status and audit details.')
                ->icon('heroicon-o-paper-airplane')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])->schema([
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge(),

                        TextEntry::make('publisher.name')
                            ->label('Published By')
                            ->placeholder('—'),

                        TextEntry::make('published_at')
                            ->label('Published At')
                            ->dateTime('d M Y H:i')
                            ->placeholder('—'),
                    ]),
                ]),

            Section::make('Record Timestamps')
                ->description('Creation and update timestamps for this result.')
                ->icon('heroicon-o-clock')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                    ])->schema([
                        TextEntry::make('created_at')->label('Created At')->dateTime('d M Y H:i'),
                        TextEntry::make('updated_at')->label('Updated At')->dateTime('d M Y H:i'),
                    ]),
                ])
                ->collapsible()
                ->collapsed(),
        ]);

    }
}
