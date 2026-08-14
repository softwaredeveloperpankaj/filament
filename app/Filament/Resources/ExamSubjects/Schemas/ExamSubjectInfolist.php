<?php

namespace App\Filament\Resources\ExamSubjects\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExamSubjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Exam & Subject')
                    ->description('Exam and subject assigned to this paper.')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                        ])
                            ->schema([
                                TextEntry::make('exam.name')
                                    ->label('Exam')
                                    ->icon('heroicon-o-document-text')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->placeholder('-'),

                                TextEntry::make('subject.name')
                                    ->label('Subject')
                                    ->icon('heroicon-o-book-open')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->placeholder('-'),

                                TextEntry::make('display_order')
                                    ->label('Display Order')
                                    ->badge()
                                    ->icon('heroicon-o-bars-3')
                                    ->placeholder('-'),
                            ]),
                    ]),

                Section::make('Marks & Duration')
                    ->description('Marks distribution and examination duration.')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 4,
                        ])
                            ->schema([
                                TextEntry::make('max_marks')
                                    ->label('Maximum Marks')
                                    ->numeric()
                                    ->badge()
                                    ->weight('bold'),

                                TextEntry::make('pass_marks')
                                    ->label('Pass Marks')
                                    ->numeric()
                                    ->badge(),

                                TextEntry::make('theory_marks')
                                    ->label('Theory Marks')
                                    ->numeric()
                                    ->badge(),

                                TextEntry::make('practical_marks')
                                    ->label('Practical Marks')
                                    ->numeric()
                                    ->badge(),

                                TextEntry::make('duration_minutes')
                                    ->label('Duration')
                                    ->suffix(' minutes')
                                    ->icon('heroicon-o-clock'),
                            ]),
                    ]),

                Section::make('Paper Structure')
                    ->description('Question and marks distribution for this subject.')
                    ->icon('heroicon-o-queue-list')
                    ->schema([
                        TextEntry::make('paper_structure')
                            ->label('Paper Structure')
                            ->placeholder('No paper structure configured.')
                            ->formatStateUsing(function ($state): string {
                                if (blank($state)) {
                                    return '-';
                                }

                                if (is_array($state)) {
                                    return json_encode(
                                        $state,
                                        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                                    );
                                }

                                if (is_string($state)) {
                                    $decoded = json_decode($state, true);

                                    if (json_last_error() === JSON_ERROR_NONE) {
                                        return json_encode(
                                            $decoded,
                                            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                                        );
                                    }

                                    return $state;
                                }

                                return (string) $state;
                            })
                            ->fontFamily('mono')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Grading')
                    ->description('Grading configuration for this subject.')
                    ->icon('heroicon-o-check-badge')
                    ->schema([
                        IconEntry::make('is_graded')
                            ->label('Graded Subject')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle'),
                    ]),

                Section::make('Record Information')
                    ->description('System timestamps for this exam subject.')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
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
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}