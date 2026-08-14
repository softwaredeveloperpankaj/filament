<?php

namespace App\Filament\Resources\BranchClasses\Schemas;

use App\Models\BranchClass;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BranchClassInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Class Information')
                    ->description('Branch and class configuration.')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                        ])
                            ->schema([
                                TextEntry::make('branch.name')
                                    ->label('Branch')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->icon('heroicon-o-building-office-2')
                                    ->columnSpanFull(),

                                TextEntry::make('name')
                                    ->label('Class Name')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->copyable()
                                    ->copyMessage('Class name copied'),

                                TextEntry::make('starting_roll_no')
                                    ->label('Roll No Prefix')
                                    ->numeric()
                                    ->badge(),
                            ]),
                    ]),

                Section::make('Record Information')
                    ->description('System timestamps for this class record.')
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