<?php

namespace App\Filament\Resources\QuestionBankItems\Schemas;

use App\Enums\DifficultyLevel;
use App\Enums\QuestionType;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuestionBankItemInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Question Location')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('questionBank.name')
                                ->label('Question Bank')
                                ->badge()
                                ->color('primary')
                                ->icon('heroicon-o-folder'),

                            TextEntry::make('subject.name')
                                ->label('Subject')
                                ->badge()
                                ->color('success')
                                ->icon('heroicon-o-book-open'),

                            TextEntry::make('topic.name')
                                ->label('Topic / Chapter')
                                ->badge()
                                ->color('gray')
                                ->placeholder('Not assigned')
                                ->icon('heroicon-o-tag'),
                        ]),
                    ]),

                Section::make('Question Details')
                    ->icon('heroicon-o-question-mark-circle')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('question_type')
                                ->label('Question Type')
                                ->badge()
                                ->icon(fn (QuestionType $state): string => match ($state) {
                                    QuestionType::MCQ => 'heroicon-o-list-bullet',
                                    QuestionType::TRUE_FALSE => 'heroicon-o-check-badge',
                                    QuestionType::FILL_BLANK => 'heroicon-o-pencil-square',
                                    QuestionType::MATCHING => 'heroicon-o-arrows-right-left',
                                    QuestionType::SHORT_ANSWER => 'heroicon-o-chat-bubble-left-right',
                                    QuestionType::LONG_ANSWER => 'heroicon-o-document-text',
                                }),

                            TextEntry::make('difficulty')
                                ->label('Difficulty')
                                ->badge(),

                            TextEntry::make('marks')
                                ->label('Marks')
                                ->badge()
                                ->color('gray')
                                ->icon('heroicon-o-trophy'),

                            TextEntry::make('negative_marks')
                                ->label('Negative Marks')
                                ->badge()
                                ->color('warning')
                                ->icon('heroicon-o-minus-circle'),

                            IconEntry::make('is_active')
                                ->label('Status')
                                ->boolean()
                                ->trueIcon('heroicon-o-check-circle')
                                ->falseIcon('heroicon-o-x-circle')
                                ->trueColor('success')
                                ->falseColor('danger'),

                            TextEntry::make('tags')
                                ->label('Tags')
                                ->badge()
                                ->separator(',')
                                ->placeholder('No tags'),
                        ]),

                        TextEntry::make('question_text')
                            ->label('Question')
                            ->markdown()
                            ->prose()
                            ->columnSpanFull(),
                    ]),

                Section::make('Options & Answers')
                    ->icon('heroicon-o-check-badge')
                    ->schema([
                        TextEntry::make('options')
                            ->label('Options')
                            ->listWithLineBreaks()
                            ->state(fn ($record) =>
                                collect($record->options ?? [])
                                    ->map(fn ($value, $key) => "{$key}. {$value}")
                                    ->values()
                                    ->all()
                            )
                            ->columnSpanFull(),

                        TextEntry::make('correct_answer')
                            ->label('Correct Answer')
                            ->badge()
                            ->color('success')
                            ->state(fn ($record) =>
                                collect($record->correct_answer ?? [])->implode(', ')
                            )
                    ]),

                Section::make('Explanation')
                    ->icon('heroicon-o-light-bulb')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('explanation')
                            ->label('Solution / Explanation')
                            ->markdown()
                            ->prose()
                            ->placeholder('No explanation provided')
                            ->columnSpanFull(),
                    ]),

                Section::make('Media')
                    ->icon('heroicon-o-photo')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        ImageEntry::make('media')
                            ->label('Attached Image / Diagram')
                            ->disk('public')
                            ->visibility('public')
                            ->placeholder('No media attached')
                            ->columnSpanFull(),
                    ]),

                Section::make('Audit Information')
                    ->icon('heroicon-o-clock')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('created_by')
                                ->label('Created By')
                                ->placeholder('System'),

                            TextEntry::make('created_at')
                                ->label('Created At')
                                ->dateTime('d M Y, h:i A')
                                ->placeholder('—'),

                            TextEntry::make('updated_at')
                                ->label('Last Updated')
                                ->dateTime('d M Y, h:i A')
                                ->placeholder('—'),
                        ]),
                    ]),
            ]);
    }
}