<?php

namespace App\Filament\Resources\ClassSections\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClassSectionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Class Section Assignment')
                    ->description('Branch, class, and section assignment details.')
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
                                    ->size('lg')
                                    ->columnSpanFull()
                                    ->placeholder('-'),

                                TextEntry::make('branchClass.name')
                                    ->label('Class')
                                    ->icon('heroicon-o-academic-cap')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->placeholder('-'),

                                TextEntry::make('section.name')
                                    ->label('Section')
                                    ->icon('heroicon-o-rectangle-group')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->placeholder('-'),

                                TextEntry::make('section.starting_roll_no')
                                    ->label('Starting Roll Number')
                                    ->badge()
                                    ->icon('heroicon-o-hashtag')
                                    ->placeholder('-'),
                            ]),
                    ]),

                Section::make('Record Information')
                    ->description('System information about this class-section assignment.')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Assigned On')
                                    ->dateTime('d M Y, h:i A')
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}