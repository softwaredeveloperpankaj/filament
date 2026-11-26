<?php

namespace App\Filament\Resources\Exports\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Export Details')
                    ->description('Review the destination, exporter, and completion time.')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2])->schema([
                            DateTimePicker::make('completed_at'),
                            TextInput::make('file_disk')->required(),
                            TextInput::make('file_name'),
                            TextInput::make('exporter')->required(),
                            Select::make('user_id')->relationship('user', 'name')->required(),
                        ]),
                    ])
                    ->collapsible()->columnSpanFull(),
                Section::make('Export Statistics')
                    ->description('Processing totals for this export job.')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 3])->schema([
                            TextInput::make('processed_rows')->required()->numeric()->default(0),
                            TextInput::make('total_rows')->required()->numeric(),
                            TextInput::make('successful_rows')->required()->numeric()->default(0),
                        ]),
                    ])
                    ->collapsible()->columnSpanFull(),
            ]);
    }
}
