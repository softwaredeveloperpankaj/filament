<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->placeholder('Full name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->placeholder('Email address')
                    ->email()
                    ->required(),
                Select::make('roles')
                    ->placeholder('Select roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),                    
                // DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->label('Password')
                    ->placeholder('Password')
                    ->password()
                    ->required()
                    ->revealable(),
            ]);
    }
}
