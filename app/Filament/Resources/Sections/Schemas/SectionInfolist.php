<?php

namespace App\Filament\Resources\Sections\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;

class SectionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('branch.name')
                    ->label('Branch'),
                TextEntry::make('name')
                    ->label('Class Name'),
                TextEntry::make('starting_roll_no')
                    ->label('Roll No Prefix')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
