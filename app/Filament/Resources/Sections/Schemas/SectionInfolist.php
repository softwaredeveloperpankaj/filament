<?php

namespace App\Filament\Resources\Sections\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SectionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Section Information')
                    ->description('Basic information about this section.')
                    ->icon('heroicon-o-rectangle-group')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                        ])
                            ->schema([
                                TextEntry::make('branch.name')
                                    ->label('Branch')
                                    ->icon('heroicon-o-building-office')
                                    ->weight('medium')
                                    ->placeholder('-'),

                                TextEntry::make('name')
                                    ->label('Section Name')
                                    ->icon('heroicon-o-rectangle-group')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->placeholder('-'),

                                TextEntry::make('starting_roll_no')
                                    ->label('Starting Roll Number')
                                    ->numeric()
                                    ->badge()
                                    ->icon('heroicon-o-hashtag')
                                    ->color('primary')
                                    ->placeholder('-'),
                            ]),
                    ]),

                Section::make('Record Information')
                    ->description('System information and timestamps.')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created At')
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