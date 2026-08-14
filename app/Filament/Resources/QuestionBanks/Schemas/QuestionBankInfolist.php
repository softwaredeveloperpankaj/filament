<?php

namespace App\Filament\Resources\QuestionBanks\Schemas;

use App\Models\QuestionBank;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuestionBankInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bank Overview')
                    ->icon('heroicon-o-folder')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('branch.name')
                                ->label('Branch')
                                ->badge()
                                ->color('primary')
                                ->icon('heroicon-o-building-office-2'),

                            TextEntry::make('subject.name')
                                ->label('Subject')
                                ->badge()
                                ->color('success')
                                ->icon('heroicon-o-book-open'),

                            TextEntry::make('type')
                                ->label('Question Mix')
                                ->badge()
                                ->formatStateUsing(fn (string $state): string => ucfirst($state))
                                ->color(fn (string $state): string => match ($state) {
                                    'objective' => 'info',
                                    'subjective' => 'warning',
                                    'mixed' => 'primary',
                                    default => 'gray',
                                })
                                ->icon(fn (string $state): string => match ($state) {
                                    'objective' => 'heroicon-o-square-3-stack-3d',
                                    'subjective' => 'heroicon-o-pencil',
                                    'mixed' => 'heroicon-o-squares-2x2',
                                    default => 'heroicon-o-question-mark-circle',
                                }),
                        ]),

                        TextEntry::make('name')
                            ->label('Question Bank Name')
                            ->size('Large')
                            ->color(fn (QuestionBank $record): string => $record->is_active ? 'info' : 'danger')
                            ->columnSpanFull(),

                        TextEntry::make('description')
                            ->label('Description')
                            ->markdown()
                            ->prose()
                            ->placeholder('No description provided for this question bank.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Question Statistics')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('items_count')
                                ->label('Total Questions')
                                ->state(fn (QuestionBank $record): int => $record->items()->count())
                                ->badge()
                                ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger')
                                ->icon('heroicon-o-document-text'),

                            TextEntry::make('active_items_count')
                                ->label('Active Questions')
                                ->state(fn (QuestionBank $record): int => $record->items()->where('is_active', true)->count())
                                ->badge()
                                ->color('info')
                                ->icon('heroicon-o-check-circle'),

                            IconEntry::make('is_active')
                                ->label('Bank Status')
                                ->boolean()
                                ->trueIcon('heroicon-o-check-circle')
                                ->falseIcon('heroicon-o-x-circle')
                                ->trueColor('success')
                                ->falseColor('danger'),
                        ]),
                    ]),

                Section::make('System Information')
                    ->icon('heroicon-o-clock')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(2)->schema([
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

                            TextEntry::make('deleted_at')
                                ->label('Deleted At')
                                ->dateTime('d M Y, h:i A')
                                ->placeholder('Not deleted')
                                ->visible(fn (QuestionBank $record): bool => $record->trashed()),
                        ]),
                    ]),
            ]);
    }
}