<?php

namespace App\Filament\Resources\Schools\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SchoolForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Select a user')
                    ->required(),
                TextInput::make('name')
                    ->label('School name')
                    ->placeholder('School name')
                    ->required(),
                TextInput::make('phone')
                    ->label('Phone')
                    ->placeholder('9876543210')
                    ->tel(),
                TextInput::make('email')
                    ->label('Email address')
                    ->placeholder('Email address')
                    ->email(),
                TextInput::make('domain_name')
                    ->label('Domain Name')
                    ->placeholder('example.com')
                    ->regex('/^(?!:\/\/)([a-zA-Z0-9-_]+\.)*[a-zA-Z0-9][a-zA-Z0-9-_]+\.[a-zA-Z]{2,11}$/')
                    ->validationMessages([
                        'regex' => 'The :attribute field must be a valid domain name (e.g., example.com).',
                    ]),
                Textarea::make('address')
                    ->label('Address')
                    ->placeholder('Address')
                    ->columnSpanFull(),
                FileUpload::make('logo')
                    ->label('School logo')
                    ->image()
                    ->directory('school-logos')
                    ->maxSize(1024)
                    ->imagePreviewHeight('250')
                    ->imagePreviewHeight('250')
                    ->columnSpanFull(),
            ])
            ->columns([
                'sm' => 1,
                'lg' => 2,
            ]);
    }
}
