<?php

namespace App\Filament\Resources\Exams\Schemas;

use App\Models\Exam;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExamInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Exam Information')
                    ->description('All exam details grouped by purpose and use, with related records shown below.')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Section::make('Core Details')
                            ->description('Basic identification and current status of the exam.')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 3,
                                ])->schema([
                                    TextEntry::make('name')
                                        ->label('Exam Name')
                                        ->weight('bold')
                                        ->size('lg')
                                        ->copyable()
                                        ->copyMessage('Exam name copied'),

                                    TextEntry::make('academicYear.name')
                                        ->label('Academic Year')
                                        ->icon('heroicon-o-calendar-days')
                                        ->placeholder('-'),

                                    TextEntry::make('mode')
                                        ->label('Exam Mode')
                                        ->badge()
                                        ->icon(fn ($state) => match ($state) {
                                            'online' => 'heroicon-o-computer-desktop',
                                            'offline' => 'heroicon-o-document-text',
                                            default => null,
                                        }),

                                    TextEntry::make('status')
                                        ->label('Status')
                                        ->badge(),

                                    TextEntry::make('slug')
                                        ->label('Slug')
                                        ->placeholder('-')
                                        ->copyable()
                                        ->copyMessage('Slug copied'),
                                ]),
                            ])
                            ->collapsible(),

                        Section::make('Academic Assignment')
                            ->description('Branch, class, and section assigned to this exam.')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 3,
                                ])->schema([
                                    TextEntry::make('branch.name')
                                        ->label('Branch')
                                        ->icon('heroicon-o-building-office-2')
                                        ->weight('bold')
                                        ->placeholder('-'),
                                ]),
                            ])
                            ->collapsible()
                            ->collapsed(),

                        Section::make('Schedule & Subject Plan')
                            ->description('Exam date range, time slot, and daily subject schedule.')
                            ->icon('heroicon-o-calendar-days')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 4,
                                ])->schema([
                                    TextEntry::make('start_date')
                                        ->label('Start Date')
                                        ->date('d M Y')
                                        ->icon('heroicon-o-calendar'),

                                    TextEntry::make('end_date')
                                        ->label('End Date')
                                        ->date('d M Y')
                                        ->icon('heroicon-o-calendar'),

                                    TextEntry::make('start_time')
                                        ->label('Daily Start Time')
                                        ->time('h:i A')
                                        ->icon('heroicon-o-clock'),

                                    TextEntry::make('end_time')
                                        ->label('Daily End Time')
                                        ->time('h:i A')
                                        ->icon('heroicon-o-clock'),
                                ]),

                                RepeatableEntry::make('schedule_rows')
                                    ->label('Subject Schedule')
                                    ->table([
                                        RepeatableEntry\TableColumn::make('Date'),
                                        RepeatableEntry\TableColumn::make('Subject'),
                                        RepeatableEntry\TableColumn::make('Start'),
                                        RepeatableEntry\TableColumn::make('End'),
                                        RepeatableEntry\TableColumn::make('Exam Place'),
                                    ])
                                    ->state(function (Exam $record): array {
                                        if ($record->schedules()->count() === 0) {
                                            return [];
                                        }

                                        return $record->schedules()
                                            ->with(['examSubject.subject'])
                                            ->orderBy('exam_date')
                                            ->orderBy('start_time')
                                            ->get()
                                            ->map(function ($schedule): array {
                                                return [
                                                    'date' => $schedule->exam_date?->format('d M Y') ?? '-',
                                                    'subject' => $schedule->examSubject?->subject?->name ?? 'Unknown subject',
                                                    'start' => $schedule->start_time?->format('h:i A') ?? '-',
                                                    'end' => $schedule->end_time?->format('h:i A') ?? '-',
                                                    'room' => $schedule->room_number ?: 'Room not assigned',
                                                ];
                                            })
                                            ->all();
                                    })
                                    ->schema([
                                        TextEntry::make('date'),
                                        TextEntry::make('subject'),
                                        TextEntry::make('start'),
                                        TextEntry::make('end'),
                                        TextEntry::make('room'),
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->collapsible()
                            ->collapsed(),

                        Section::make('Configuration & Usage')
                            ->description('Exam settings, operational summary, and participation counts.')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                ])->schema([
                                    IconEntry::make('is_practical')
                                        ->label('Practical Component')
                                        ->boolean(),

                                    TextEntry::make('subjects_count')
                                        ->label('Subjects')
                                        ->state(fn (Exam $record): int => $record->subjects()->count())
                                        ->badge(),

                                    TextEntry::make('studentEntries_count')
                                        ->label('Students')
                                        ->state(fn (Exam $record): int => $record->studentEntries()->count())
                                        ->badge(),
                                ]),

                                KeyValueEntry::make('settings')
                                    ->label('Online Exam Settings')
                                    ->keyLabel('Setting')
                                    ->valueLabel('Value')
                                    ->visible(fn (Exam $record): bool => $record->mode === 'online')
                                    ->columnSpanFull(),
                            ])
                            ->collapsible()
                            ->collapsed(),

                        Section::make('Instructions & Notes')
                            ->description('Instructions provided to students and important communication.')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                TextEntry::make('instructions')
                                    ->label('Student Instructions')
                                    ->placeholder('No instructions provided.')
                                    ->prose()
                                    ->columnSpanFull(),
                            ])
                            ->collapsible()
                            ->collapsed(),

                        Section::make('Record Metadata')
                            ->description('System and audit information for tracking and admin use.')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 3,
                                ])->schema([
                                    TextEntry::make('created_at')
                                        ->label('Created')
                                        ->dateTime('d M Y, h:i A')
                                        ->placeholder('-'),

                                    TextEntry::make('updated_at')
                                        ->label('Last Updated')
                                        ->dateTime('d M Y, h:i A')
                                        ->placeholder('-'),

                                    TextEntry::make('createdBy.name')
                                        ->label('Created By')
                                        ->icon('heroicon-o-user')
                                        ->placeholder('-'),
                                ]),
                            ])
                            ->collapsible()
                            ->collapsed(),
                    ])
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed(true),
            ]);
    }
}