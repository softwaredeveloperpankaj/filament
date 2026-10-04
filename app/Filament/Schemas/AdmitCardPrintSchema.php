<?php

namespace App\Filament\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class AdmitCardPrintSchema
{
    public static function getComponents(): array
    {
        return [
            Section::make('Filter Admit Cards')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('exam_id')
                            ->relationship('exam', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('section_id')
                            ->relationship('section', 'name')
                            ->nullable()
                            ->searchable()
                            ->preload(),

                        DatePicker::make('print_date')->default(now()),
                    ]),
                ]),
        ];
    }
}