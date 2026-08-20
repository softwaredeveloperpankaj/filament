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
                    ->label('Academic Year Name')
                    ->placeholder('e.g. 2023-2024')
                    ->required(),
                TextInput::make('settings')
                    ->label('Settings'),
                DatePicker::make('start_date')
                    ->label('Start Date')
                    ->required(),
                DatePicker::make('end_date')
                    ->label('End Date')
                    ->required(),
                Select::make('status')
                    ->label('Status')
                    ->options(AcademicYearStatus::forFilamentSelect())
                    ->default('upcoming')
                    ->required(),
                Select::make('term_type')
                    ->label('Term Type')
                    ->options(AcademicTermType::forFilamentSelect())
                    ->default('annual')
                    ->required(),
                Toggle::make('is_current')
                    ->label('Is Current Academic Year')
                    ->required(),
            ]);
    }
}
