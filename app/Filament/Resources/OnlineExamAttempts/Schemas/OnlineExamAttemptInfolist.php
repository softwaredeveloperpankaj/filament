<?php

namespace App\Filament\Resources\OnlineExamAttempts\Schemas;

use App\Enums\AttemptStatus;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OnlineExamAttemptInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Attempt Overview')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('entry.student.name')
                                ->label('Student')
                                ->placeholder('—')
                                ->weight('medium'),

                            TextEntry::make('entry.roll_no')
                                ->label('Roll No')
                                ->badge()
                                ->color('gray')
                                ->placeholder('—'),

                            TextEntry::make('exam.name')
                                ->label('Exam'),

                            TextEntry::make('examSubject.subject.name')
                                ->label('Subject')
                                ->placeholder('—'),

                            TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->color(fn(AttemptStatus $state) => match ($state) {
                                    AttemptStatus::NOT_STARTED => 'gray',
                                    AttemptStatus::IN_PROGRESS => 'info',
                                    AttemptStatus::SUBMITTED => 'success',
                                    AttemptStatus::AUTO_SUBMITTED => 'warning',
                                    AttemptStatus::TERMINATED => 'danger',
                                }),

                            TextEntry::make('ip_address')
                                ->label('IP Address')
                                ->placeholder('—'),
                        ]),
                    ]),

                Section::make('Timing')
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('started_at')
                                ->label('Started At')
                                ->dateTime('d M Y H:i:s')
                                ->placeholder('Not started'),

                            TextEntry::make('submitted_at')
                                ->label('Submitted At')
                                ->dateTime('d M Y H:i:s')
                                ->placeholder('—'),

                            TextEntry::make('ended_at')
                                ->label('Ended At')
                                ->dateTime('d M Y H:i:s')
                                ->placeholder('—'),

                            TextEntry::make('time_spent')
                                ->label('Time Spent')
                                ->getStateUsing(fn($record) => gmdate('H:i:s', $record->time_spent))
                                ->badge()
                                ->color('gray'),
                        ]),
                    ]),

                Section::make('Scoring')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('total_obtained')
                                ->label('Marks Obtained')
                                ->numeric()
                                ->badge()
                                ->color(fn($record) => $record->percentage >= 40 ? 'success' : 'danger'),

                            TextEntry::make('total_maximum')
                                ->label('Max Marks')
                                ->numeric()
                                ->badge()
                                ->color('gray'),

                            TextEntry::make('percentage')
                                ->label('Percentage')
                                ->numeric()
                                ->suffix('%'),
                        ]),
                    ]),

                Section::make('Answers Submitted')
                    ->schema([
                        KeyValueEntry::make('answers')
                            ->label('Question ID → Answer')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Auto-Graded Scores')
                    ->schema([
                        KeyValueEntry::make('auto_score')
                            ->label('Question ID → Marks Obtained')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Technical Details')
                    ->schema([
                        Grid::make(1)->schema([
                            TextEntry::make('user_agent')
                                ->label('User Agent')
                                ->placeholder('—')
                                ->copyable(),

                            KeyValueEntry::make('time_spent_per_question')
                                ->label('Time Spent per Question (seconds)')
                                ->placeholder('No data recorded'),
                        ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Record Timestamps')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('created_at')
                                ->label('Created At')
                                ->dateTime('d M Y H:i:s'),

                            TextEntry::make('updated_at')
                                ->label('Updated At')
                                ->dateTime('d M Y H:i:s'),
                        ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}