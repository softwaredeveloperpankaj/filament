<?php

namespace App\Filament\Resources\Imports\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ImportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Import Details')->columns(2)->schema([
                    TextEntry::make('importer')->label('Resource')
                        ->formatStateUsing(fn ($state) => class_basename($state))->badge()->color('info'),
                    TextEntry::make('user.name')->label('Imported By'),
                    TextEntry::make('file_name')->label('File Name'),
                    TextEntry::make('file_path')->label('File Path'),
                    TextEntry::make('status')->label('Status')
                        ->getStateUsing(fn ($record) => $record->status)->badge()
                        ->color(fn (string $state) => match($state) {
                            'Completed' => 'success', 'In Progress' => 'warning', default => 'gray',
                        }),
                    TextEntry::make('duration')->label('Duration')
                        ->getStateUsing(fn ($record) => $record->duration ?? '—'),
                ]),
                Section::make('Row Statistics')->columns(3)->schema([
                    TextEntry::make('total_rows')->label('Total Rows')->numeric(),
                    TextEntry::make('processed_rows')->label('Processed')->numeric(),
                    TextEntry::make('successful_rows')->label('Successful')->numeric()->color('success'),
                    TextEntry::make('failed_rows_count')->label('Failed')
                        ->getStateUsing(fn ($record) => $record->total_rows - $record->successful_rows)
                        ->color(fn (int $state) => $state > 0 ? 'danger' : 'success'),
                    TextEntry::make('created_at')->label('Started At')->dateTime('d M Y, h:i:s A'),
                    TextEntry::make('completed_at')->label('Completed At')
                        ->dateTime('d M Y, h:i:s A')->placeholder('Not yet'),
                ]),
            ]);
    }
}
