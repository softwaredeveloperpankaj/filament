<?php

namespace App\Filament\Schemas;

use App\Enums\MarksSource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class ExamMarkEntryFormSchema
{
    public static function getComponents(): array
    {
        return [
            Section::make('Mark Entry')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('theory_obtained')->numeric()->default(0),
                        TextInput::make('practical_obtained')->numeric()->default(0),
                        TextInput::make('internal_obtained')->numeric()->default(0),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('total_obtained')->numeric()->disabled(),
                        TextInput::make('percentage')->numeric()->disabled(),
                        TextInput::make('grade')->disabled(),
                    ]),
                    Grid::make(2)->schema([
                        Select::make('source')
                            ->options(MarksSource::class)
                            ->default(MarksSource::OFFLINE_MANUAL),
                        Toggle::make('is_locked')->label('Lock from editing'),
                    ]),
                ]),
        ];
    }
}