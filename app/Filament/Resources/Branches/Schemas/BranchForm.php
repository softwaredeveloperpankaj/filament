<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('school_id')
                    ->relationship('school', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->placeholder('Select a school'),
                Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Select a user')
                    ->required(),
                TextInput::make('name')
                    ->label('Branch name')
                    ->placeholder('Branch name')
                    ->required(),
                TextInput::make('code')
                    ->label('Branch code')
                    ->placeholder('Branch code'),
                TextInput::make('email')
                    ->label('Email address')
                    ->placeholder('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->label('Phone')
                    ->placeholder('9876543210')
                    ->tel(),
                Textarea::make('address')
                    ->label('Address')
                    ->placeholder('Address')
                    ->columnSpanFull(),
                Toggle::make('is_main')
                    ->onIcon('heroicon-o-check-circle')
                    ->offIcon('heroicon-o-x-circle')
                    ->columnSpan(2),
                Toggle::make('is_active')
                    ->onIcon('heroicon-o-check-circle')
                    ->offIcon('heroicon-o-x-circle'),
            ])->columns([
                'sm' => 1,
                'lg' => 2,
            ]);
    }
}
