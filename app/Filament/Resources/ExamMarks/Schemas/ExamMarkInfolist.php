<?php

namespace App\Filament\Resources\ExamMarks\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ExamMarkInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Reference')
                ->description('Student, examination, and subject reference.')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])->schema([
                        TextEntry::make('entry.student.name')->label('Student'),
                        TextEntry::make('entry.roll_no')->label('Roll No')->badge()->color('gray'),
                        TextEntry::make('exam.name')->label('Exam'),
                        TextEntry::make('examSubject.subject.name')->label('Subject'),
                    ]),
                ]),

            Section::make('Marks Breakdown')
                ->description('Obtained, maximum, percentage, and pass status.')
                ->icon('heroicon-o-chart-bar')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])->schema([
                        TextEntry::make('theory_obtained')
                            ->label('Theory')
                            ->formatStateUsing(fn($state, $record) => "{$state} / {$record->theory_maximum}")
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('practical_obtained')
                            ->label('Practical')
                            ->formatStateUsing(fn($state, $record) => "{$state} / {$record->practical_maximum}")
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('internal_obtained')
                            ->label('Internal')
                            ->formatStateUsing(fn($state, $record) => "{$state} / {$record->internal_maximum}")
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('total_obtained')
                            ->label('Total Obtained')
                            ->formatStateUsing(fn($state, $record) => "{$state} / {$record->total_maximum}")
                            ->badge()
                            ->color(fn($record) => $record->is_passed ? 'success' : 'danger')
                            ->weight('bold'),

                        TextEntry::make('percentage')
                            ->label('Percentage')
                            ->suffix('%'),

                        IconEntry::make('is_passed')
                            ->label('Result')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                    ]),
                ]),

            Section::make('Grading')
                ->description('Grade, source, attendance flags, lock status, and remarks.')
                ->icon('heroicon-o-academic-cap')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                        'xl' => 4,
                    ])->schema([
                        TextEntry::make('grade')
                            ->label('Grade')
                            ->badge(),

                        TextEntry::make('source')
                            ->label('Marks Source')
                            ->badge(),

                        IconEntry::make('absent')
                            ->label('Absent')
                            ->boolean(),

                        IconEntry::make('exempted')
                            ->label('Exempted')
                            ->boolean(),

                        IconEntry::make('is_locked')
                            ->label('Locked')
                            ->boolean()
                            ->trueIcon('heroicon-o-lock-closed')
                            ->falseIcon('heroicon-o-lock-open')
                            ->trueColor('danger')
                            ->falseColor('success'),

                        TextEntry::make('remarks')
                            ->label('Remarks')
                            ->columnSpanFull()
                            ->placeholder('—'),
                    ]),
                ]),

            Section::make('Audit Trail')
                ->description('Grader and grading timestamp information.')
                ->icon('heroicon-o-clock')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                    ])->schema([
                        TextEntry::make('grader.name')
                            ->label('Graded By')
                            ->placeholder('—'),

                        TextEntry::make('graded_at')
                            ->label('Graded At')
                            ->dateTime('d M Y H:i')
                            ->placeholder('—'),
                    ]),
                ])
                ->collapsible()
                ->collapsed(),
        ]);
    }
}