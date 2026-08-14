<?php

namespace App\Filament\Resources\Subjects\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Subject Details')
                    ->description('Basic information about this subject')
                    ->icon('heroicon-o-book-open')
                    ->columns([
                        'sm' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        TextEntry::make('branch.name')
                            ->label('Branch')
                            ->weight('medium'),

                        TextEntry::make('name')
                            ->label('Subject Name')
                            ->weight('medium'),

                        TextEntry::make('code')
                            ->label('Subject Code')
                            ->badge()
                            ->color('primary'),
                    ]),

                Section::make('Record Information')
                    ->description('Subject record timestamps')
                    ->icon('heroicon-o-information-circle')
                    ->columns([
                        'sm' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created On')
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('—'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('—'),
                    ]),
            ]);
    }
}