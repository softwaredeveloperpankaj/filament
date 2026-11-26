<?php

namespace App\Filament\Resources\Subjects\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Subject Details')
                    ->description('Basic information about this subject.')
                    ->icon('heroicon-o-book-open')
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
                                    ->weight('medium'),

                                TextEntry::make('name')
                                    ->label('Subject Name')
                                    ->weight('bold')
                                    ->size('lg'),

                                TextEntry::make('code')
                                    ->label('Subject Code')
                                    ->badge()
                                    ->icon('heroicon-o-hashtag')
                                    ->color('primary'),
                            ]),
                    ]),

                Section::make('Record Information')
                    ->description('Subject record timestamps.')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created On')
                                    ->dateTime('d M Y, h:i A')
                                    ->placeholder('—'),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('d M Y, h:i A')
                                    ->placeholder('—'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}