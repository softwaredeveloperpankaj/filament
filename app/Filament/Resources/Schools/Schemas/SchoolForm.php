<?php

namespace App\Filament\Resources\Schools\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SchoolForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('address'),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('domain_name')
                    ->label('Domain Name')
                    ->placeholder('example.com')
                    ->regex('/^(?!:\/\/)([a-zA-Z0-9-_]+\.)*[a-zA-Z0-9][a-zA-Z0-9-_]+\.[a-zA-Z]{2,11}$/')
                    ->validationMessages([
                        'regex' => 'The :attribute field must be a valid domain name (e.g., example.com).',
                    ]),
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
                'xl' => 3,
            ]);
    }
}
