<?php

namespace App\Filament\Resources\Branches\Schemas;

use App\Models\Branch;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BranchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Branch Information')
                    ->description('Basic branch and organization details.')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                        ])
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Branch Name')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->copyable()
                                    ->copyMessage('Branch name copied'),

                                TextEntry::make('code')
                                    ->label('Branch Code')
                                    ->badge()
                                    ->placeholder('-')
                                    ->copyable()
                                    ->copyMessage('Branch code copied'),

                                TextEntry::make('school.name')
                                    ->label('School')
                                    ->icon('heroicon-o-academic-cap')
                                    ->placeholder('-'),

                                TextEntry::make('user.name')
                                    ->label('Assigned User')
                                    ->icon('heroicon-o-user')
                                    ->placeholder('-'),
                            ]),
                    ]),

                Section::make('Contact Information')
                    ->description('Branch contact details and location.')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])
                            ->schema([
                                TextEntry::make('email')
                                    ->label('Email Address')
                                    ->icon('heroicon-o-envelope')
                                    ->placeholder('-')
                                    ->copyable()
                                    ->copyMessage('Email address copied'),

                                TextEntry::make('phone')
                                    ->label('Phone')
                                    ->icon('heroicon-o-phone')
                                    ->placeholder('-')
                                    ->copyable()
                                    ->copyMessage('Phone number copied'),

                                TextEntry::make('address')
                                    ->label('Address')
                                    ->icon('heroicon-o-map-pin')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Branch Status')
                    ->description('Current branch configuration and status.')
                    ->icon('heroicon-o-check-badge')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])
                            ->schema([
                                IconEntry::make('is_main')
                                    ->label('Main Branch')
                                    ->boolean(),

                                IconEntry::make('is_active')
                                    ->label('Active')
                                    ->boolean(),
                            ]),
                    ]),

                Section::make('Record Information')
                    ->description('System timestamps for this branch.')
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