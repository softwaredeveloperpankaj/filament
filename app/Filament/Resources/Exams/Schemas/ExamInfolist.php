<?php

namespace App\Filament\Resources\Exams\Schemas;

use App\Models\Exam;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
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
                /*
                 * ─────────────────────────────────────────────
                 * Exam Overview
                 * ─────────────────────────────────────────────
                 */
                Section::make('Exam Overview')
                    ->description('Basic information and current exam status.')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                        ])
                            ->schema([
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
                    ]),

                /*
                 * ─────────────────────────────────────────────
                 * Branch & Class
                 * ─────────────────────────────────────────────
                 */
                Section::make('Class & Branch')
                    ->description('Branch, class, and section assigned to this exam.')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                        ])
                            ->schema([
                                TextEntry::make('branch.name')
                                    ->label('Branch')
                                    ->icon('heroicon-o-building-office-2')
                                    ->weight('bold')
                                    ->placeholder('-'),

                                TextEntry::make('branchClass.name')
                                    ->label('Class')
                                    ->icon('heroicon-o-academic-cap')
                                    ->weight('bold')
                                    ->placeholder('-'),

                                TextEntry::make('section.name')
                                    ->label('Section')
                                    ->icon('heroicon-o-rectangle-group')
                                    ->weight('bold')
                                    ->placeholder('-'),
                            ]),
                    ]),

                /*
                 * ─────────────────────────────────────────────
                 * Schedule
                 * ─────────────────────────────────────────────
                 */
                Section::make('Exam Schedule')
                    ->description('Exam dates and daily timing.')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 4,
                        ])
                            ->schema([
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
                    ]),

                /*
                 * ─────────────────────────────────────────────
                 * Exam Configuration
                 * ─────────────────────────────────────────────
                 */
                Section::make('Exam Configuration')
                    ->description('Additional exam options and online settings.')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])
                            ->schema([
                                IconEntry::make('is_practical')
                                    ->label('Practical Component')
                                    ->boolean(),

                                TextEntry::make('subjects_count')
                                    ->label('Subjects')
                                    ->state(
                                        fn (Exam $record): int => $record->subjects()->count()
                                    )
                                    ->badge(),

                                TextEntry::make('studentEntries_count')
                                    ->label('Students')
                                    ->state(
                                        fn (Exam $record): int => $record->studentEntries()->count()
                                    )
                                    ->badge(),
                            ]),

                        KeyValueEntry::make('settings')
                            ->label('Online Exam Settings')
                            ->keyLabel('Setting')
                            ->valueLabel('Value')
                            ->visible(
                                fn (Exam $record): bool =>
                                    $record->mode === 'online'
                            )
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                /*
                 * ─────────────────────────────────────────────
                 * Instructions
                 * ─────────────────────────────────────────────
                 */
                Section::make('Instructions')
                    ->description('Instructions provided to students.')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextEntry::make('instructions')
                            ->label('Student Instructions')
                            ->placeholder('No instructions provided.')
                            ->prose()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                /*
                 * ─────────────────────────────────────────────
                 * Record Information
                 * ─────────────────────────────────────────────
                 */
                Section::make('Record Information')
                    ->description('System information about this exam.')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                        ])
                            ->schema([
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
            ]);
    }
}