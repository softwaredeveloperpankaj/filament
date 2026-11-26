<?php

namespace App\Filament\Resources\Sections\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class SectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Section Details')
                    ->description('Configure the branch, section name, and starting roll number.')
                    ->icon('heroicon-o-squares-2x2')
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2])->schema([
                            Select::make('branch_id')
                                ->label('Select Branch')
                                ->relationship('branch', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->placeholder('Select a branch'),
                            TextInput::make('name')
                                ->required()
                                ->placeholder('Class name'),
                            TextInput::make('starting_roll_no')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(9999)
                                ->placeholder('Roll number prefix'),
                        ]),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),
            ]);
    }
}
