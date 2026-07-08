<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('email')
                    ->label('Email address'),
                // TextEntry::make('email_verified_at')
                //     ->dateTime()
                //     ->placeholder('-'),
                TextEntry::make('role')
                    ->label('Role')
                    ->disabled()
                    ->getStateUsing(fn ($record) => $record->getRoleNames()->first() ?? 'User')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'super_admin' => 'danger',
                        'admin' => 'success',
                        'teacher' => 'info',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => str($state)->replace('_', ' ')->title()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
