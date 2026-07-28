<?php

namespace App\Filament\Resources\Exports\Schemas;

use App\Models\Export;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Export Details')->columns(2)->schema([
                    TextEntry::make('exporter')
                        ->label('Resource')
                        ->getStateUsing(fn (Export $record): string => class_basename($record->exporter))
                        ->badge()
                        ->color('warning'),
                    TextEntry::make('user.name')->label('Exported By'),
                    TextEntry::make('file_name')->label('File Name'),
                    TextEntry::make('file_path')->label('File Path'),
                    TextEntry::make('status')
                        ->label('Status')
                        ->getStateUsing(fn (Export $record): string => $record->status ?? 'Pending')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'Completed'   => 'success',
                            'In Progress' => 'warning',
                            default       => 'gray',
                        }),
                    TextEntry::make('duration')
                        ->label('Duration')
                        ->getStateUsing(fn (Export $record): string => $record->duration ?? '—'),
                ]),
                Section::make('Row Statistics')->columns(3)->schema([
                    TextEntry::make('total_rows')
                        ->label('Total Rows')
                        ->numeric(),
                    TextEntry::make('processed_rows')->label('Processed')->numeric(),
                    TextEntry::make('successful_rows')->label('Successful')->numeric()->color('success'),
                    TextEntry::make('failed_rows_count')
                        ->label('Failed Rows')
                        ->getStateUsing(fn (Export $record): int => $record->total_rows - $record->successful_rows)
                        ->color(fn (string $state): string => (int) $state > 0 ? 'danger' : 'success'),
                    TextEntry::make('created_at')->label('Started At')->dateTime('d M Y, h:i:s A'),
                    TextEntry::make('completed_at')->label('Completed At')
                        ->dateTime('d M Y, h:i:s A')->placeholder('Not yet'),
                ]),
            ]);
    }
}
