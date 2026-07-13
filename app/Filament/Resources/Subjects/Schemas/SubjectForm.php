<?php

namespace App\Filament\Resources\Subjects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubjectForm
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
                    ->placeholder('Subject name'),
                TextInput::make('code')
                    ->required()
                    ->placeholder('Subject code'),
            ]);
    }
}
