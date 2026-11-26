<?php

namespace App\Filament\Resources\AcademicYears\Schemas;

use App\Models\AcademicYear;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AcademicYearInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Academic Year Information')
                    ->description('Academic year details, configuration, and status.')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Section::make('Reference Details')
                            ->description('Identification and current status of the academic year.')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 3,
                                ])->schema([
                                    TextEntry::make('name')
                                        ->label('Academic Year')
                                        ->weight('bold')
                                        ->size('lg')
                                        ->copyable()
                                        ->copyMessage('Academic year copied'),

                                    TextEntry::make('status')
                                        ->label('Status')
                                        ->badge()
                                        ->color(fn ($state) => match ($state?->value ?? $state) {
                                            'active', 'current' => 'success',
                                            'inactive', 'closed' => 'gray',
                                            'draft' => 'warning',
                                            default => 'primary',
                                        }),

                                    TextEntry::make('term_type')
                                        ->label('Term Type')
                                        ->badge()
                                        ->color('primary'),

                                    IconEntry::make('is_current')
                                        ->label('Current Academic Year')
                                        ->boolean()
                                        ->trueIcon('heroicon-o-check-circle')
                                        ->falseIcon('heroicon-o-x-circle')
                                        ->trueColor('success')
                                        ->falseColor('gray'),
                                ]),
                            ])
                            ->collapsible(),

                        Section::make('Academic Timeline')
                            ->description('Start and end dates for this academic year.')
                            ->icon('heroicon-o-calendar-days')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 3,
                                ])->schema([
                                    TextEntry::make('start_date')
                                        ->label('Start Date')
                                        ->date('d M Y')
                                        ->icon('heroicon-o-calendar')
                                        ->placeholder('-'),

                                    TextEntry::make('end_date')
                                        ->label('End Date')
                                        ->date('d M Y')
                                        ->icon('heroicon-o-calendar')
                                        ->placeholder('-'),
                                ]),
                            ])
                            ->collapsible(),

                        Section::make('Additional Settings')
                            ->description('Extra configuration associated with this academic year.')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                TextEntry::make('settings')
                                    ->label('Settings')
                                    ->placeholder('No settings configured')
                                    ->prose()
                                    ->columnSpanFull(),
                            ])
                            ->collapsible(),

                        Section::make('Record Information')
                            ->description('System timestamps and record history.')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 3,
                                ])->schema([
                                    TextEntry::make('created_at')
                                        ->label('Created')
                                        ->dateTime('d M Y, h:i A')
                                        ->placeholder('-'),

                                    TextEntry::make('updated_at')
                                        ->label('Last Updated')
                                        ->dateTime('d M Y, h:i A')
                                        ->placeholder('-'),

                                    TextEntry::make('deleted_at')
                                        ->label('Deleted')
                                        ->dateTime('d M Y, h:i A')
                                        ->placeholder('-')
                                        ->visible(
                                            fn (AcademicYear $record): bool => $record->trashed()
                                        ),
                                ]),
                            ])
                            ->collapsible()
                            ->collapsed(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}