<?php

namespace App\Filament\Resources\Schools\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SchoolInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('School Information')
                    ->description('Basic information about the school.')
                    ->icon('heroicon-o-building-library')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                        ])
                            ->schema([
                                ImageEntry::make('logo')
                                    ->label('School Logo')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->imageWidth(140)
                                    ->imageHeight(140)
                                    ->circular()
                                    ->placeholder('-'),

                                TextEntry::make('name')
                                    ->label('School Name')
                                    ->icon('heroicon-o-building-library')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->placeholder('-'),

                                TextEntry::make('user.name')
                                    ->label('Created By')
                                    ->icon('heroicon-o-user')
                                    ->weight('medium')
                                    ->placeholder('-'),
                            ]),
                    ]),

                Section::make('Contact Information')
                    ->description('School contact and communication details.')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                        ])
                            ->schema([
                                TextEntry::make('phone')
                                    ->label('Phone')
                                    ->icon('heroicon-o-phone')
                                    ->placeholder('-'),

                                TextEntry::make('email')
                                    ->label('Email Address')
                                    ->icon('heroicon-o-envelope')
                                    ->placeholder('-')
                                    ->copyable()
                                    ->copyMessage('Email address copied'),

                                TextEntry::make('domain_name')
                                    ->label('Domain Name')
                                    ->icon('heroicon-o-globe-alt')
                                    ->placeholder('-')
                                    ->copyable()
                                    ->copyMessage('Domain copied'),

                                TextEntry::make('address')
                                    ->label('Address')
                                    ->icon('heroicon-o-map-pin')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
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