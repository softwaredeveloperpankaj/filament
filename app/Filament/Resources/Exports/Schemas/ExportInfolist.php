<?php

namespace App\Filament\Resources\Exports\Schemas;

use App\Models\Export;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Export Details')
                    ->description('Information about the exported resource and generated file.')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                        ])
                            ->schema([
                                TextEntry::make('exporter')
                                    ->label('Resource')
                                    ->getStateUsing(
                                        fn (Export $record): string =>
                                            class_basename($record->exporter)
                                    )
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-table-cells'),

                                TextEntry::make('user.name')
                                    ->label('Exported By')
                                    ->icon('heroicon-o-user')
                                    ->weight('medium')
                                    ->placeholder('-'),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->getStateUsing(
                                        fn (Export $record): string =>
                                            $record->status ?? 'Pending'
                                    )
                                    ->badge()
                                    ->color(
                                        fn (string $state): string => match ($state) {
                                            'Completed' => 'success',
                                            'In Progress' => 'warning',
                                            'Failed' => 'danger',
                                            default => 'gray',
                                        }
                                    ),

                                TextEntry::make('file_name')
                                    ->label('File Name')
                                    ->placeholder('Not generated')
                                    ->copyable()
                                    ->copyMessage('File name copied')
                                    ->icon('heroicon-o-document'),

                                TextEntry::make('file_disk')
                                    ->label('Storage Disk')
                                    ->badge()
                                    ->placeholder('-'),

                                TextEntry::make('duration')
                                    ->label('Duration')
                                    ->getStateUsing(
                                        fn (Export $record): string =>
                                            $record->duration ?? '—'
                                    )
                                    ->icon('heroicon-o-clock'),
                            ]),
                    ]),

                Section::make('Row Statistics')
                    ->description('Processing and export results.')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Grid::make([
                            'default' => 2,
                            'sm' => 2,
                            'lg' => 4,
                        ])
                            ->schema([
                                TextEntry::make('total_rows')
                                    ->label('Total Rows')
                                    ->numeric()
                                    ->weight('bold')
                                    ->size('lg'),

                                TextEntry::make('processed_rows')
                                    ->label('Processed')
                                    ->numeric()
                                    ->weight('bold')
                                    ->size('lg'),

                                TextEntry::make('successful_rows')
                                    ->label('Successful')
                                    ->numeric()
                                    ->weight('bold')
                                    ->size('lg')
                                    ->color('success'),

                                TextEntry::make('failed_rows_count')
                                    ->label('Failed')
                                    ->getStateUsing(
                                        fn (Export $record): int =>
                                            max(
                                                0,
                                                $record->total_rows - $record->successful_rows
                                            )
                                    )
                                    ->numeric()
                                    ->weight('bold')
                                    ->size('lg')
                                    ->color(
                                        fn (string $state): string =>
                                            (int) $state > 0
                                                ? 'danger'
                                                : 'success'
                                    ),
                            ]),
                    ]),

                Section::make('File Information')
                    ->description('Generated export file information.')
                    ->icon('heroicon-o-document-arrow-down')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])
                            ->schema([
                                TextEntry::make('file_name')
                                    ->label('File Name')
                                    ->placeholder('Not generated')
                                    ->copyable()
                                    ->copyMessage('File name copied'),

                                TextEntry::make('file_path')
                                    ->label('File Path')
                                    ->placeholder('-')
                                    ->copyable()
                                    ->copyMessage('File path copied'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Export Timeline')
                    ->description('Export processing timestamps.')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Started At')
                                    ->dateTime('d M Y, h:i:s A')
                                    ->placeholder('-'),

                                TextEntry::make('completed_at')
                                    ->label('Completed At')
                                    ->dateTime('d M Y, h:i:s A')
                                    ->placeholder('Not yet'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}