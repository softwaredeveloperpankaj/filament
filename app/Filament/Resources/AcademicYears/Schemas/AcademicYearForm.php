<?php

namespace App\Filament\Resources\AcademicYears\Schemas;

use App\Enums\AcademicTermType;
use App\Enums\AcademicYearStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AcademicYearForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date')
                    ->required(),
                Toggle::make('is_current')
                    ->required(),
                Select::make('status')
                    ->options(AcademicYearStatus::class)
                    ->default('upcoming')
                    ->required(),
                Select::make('term_type')
                    ->options(AcademicTermType::class)
                    ->default('annual')
                    ->required(),
                TextInput::make('settings'),
            ]);
    }
}
