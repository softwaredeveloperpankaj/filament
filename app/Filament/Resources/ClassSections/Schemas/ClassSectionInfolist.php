<?php

namespace App\Filament\Resources\ClassSections\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ClassSectionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('branch.name')
                    ->label('Branch Name'),
                TextEntry::make('branchClass.name')
                    ->label('Class Name'),
                TextEntry::make('section.name')
                    ->label('Section Name'),
                TextEntry::make('section.starting_roll_no')
                    ->label('Starting Roll Number for Section'),
                TextEntry::make('created_at')
                    ->label('Created At')
            ]);
    }
}
