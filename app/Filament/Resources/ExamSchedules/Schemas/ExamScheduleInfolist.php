<?php

namespace App\Filament\Resources\ExamSchedules\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExamScheduleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Exam Reference')
                ->description('Exam, branch, subject, and grading reference.')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])->schema([
                        TextEntry::make('exam.name')
                            ->label('Exam'),

                        TextEntry::make('exam.branch.name')
                            ->label('Branch'),

                        TextEntry::make('examSubject.subject.name')
                            ->label('Subject')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('examSubject.duration_minutes')
                            ->label('Duration (mins)')
                            ->badge()
                            ->color('gray'),

                        IconEntry::make('examSubject.is_graded')
                            ->label('Graded Subject')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('gray'),
                    ]),
                ]),

            Section::make('Schedule Details')
                ->description('Date, timing, room, and invigilation information.')
                ->icon('heroicon-o-calendar-days')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])->schema([
                        TextEntry::make('exam_date')
                            ->label('Exam Date')
                            ->date('l, d M Y')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('start_time')
                            ->label('Start Time')
                            ->formatStateUsing(fn($state) => $state?->format('H:i')),

                        TextEntry::make('end_time')
                            ->label('End Time')
                            ->formatStateUsing(fn($state) => $state?->format('H:i')),

                        TextEntry::make('duration')
                            ->label('Duration')
                            ->formatStateUsing(fn($state) => "{$state} minutes")
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('room_number')
                            ->label('Room / Hall')
                            ->placeholder('—')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('invigilator.name')
                            ->label('Invigilator')
                            ->placeholder('—'),
                    ]),
                ]),

            Section::make('Instructions')
                ->description('Additional instructions for this scheduled paper.')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->schema([
                    TextEntry::make('instructions')
                        ->label('Subject-Specific Instructions')
                        ->columnSpanFull()
                        ->placeholder('No special instructions.'),
                ])
                ->collapsible()
                ->collapsed(fn($record) => blank($record->instructions)),

            Section::make('Timestamps')
                ->description('When this schedule was created and updated.')
                ->icon('heroicon-o-clock')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                    ])->schema([
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('d M Y H:i'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('d M Y H:i'),
                    ]),
                ])
                ->collapsible()
                ->collapsed(),
        ]);
    }
}
