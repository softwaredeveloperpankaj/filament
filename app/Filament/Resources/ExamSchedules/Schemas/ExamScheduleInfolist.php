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
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('exam.name')
                            ->label('Exam'),

                        TextEntry::make('exam.branchClass.name')
                            ->label('Class'),

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
                ->schema([
                    Grid::make(3)->schema([
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
                ->schema([
                    TextEntry::make('instructions')
                        ->label('Subject-Specific Instructions')
                        ->columnSpanFull()
                        ->placeholder('No special instructions.'),
                ])
                ->collapsible()
                ->collapsed(fn($record) => blank($record->instructions)),

            Section::make('Timestamps')
                ->schema([
                    Grid::make(2)->schema([
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
