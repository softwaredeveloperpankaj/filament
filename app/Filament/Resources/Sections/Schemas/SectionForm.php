<?php

namespace App\Filament\Resources\Sections\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class SectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
            ]);
    }
}
