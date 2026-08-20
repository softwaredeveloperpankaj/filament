<?php

namespace App\Filament\Resources\OnlineExamAttempts\Schemas;

use App\Enums\AttemptStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OnlineExamAttemptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Attempt Info (Read-Only)')
                    ->description('Basic attempt details cannot be edited — they are system-generated.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('entry.student.name')
                                ->label('Student')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('examSubject.subject.name')
                                ->label('Subject')
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                    ]),

                    Section::make('Manual Override')
                        ->description('Only adjust these fields for dispute resolution or technical issue correction.')
                        ->schema([
                            Grid::make(2)->schema([

                                Select::make('status')
                                    ->label('Status')
                                    ->options(AttemptStatus::forFilamentSelect())
                                    ->required()
                                    ->helperText('Changing status manually should be logged/audited.'),

                                TextInput::make('total_obtained')
                                    ->label('Total Obtained (Manual Override)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->helperText('Overrides auto-calculated score. Use with caution.'),
                            ]),
                        ]),
            ]);
    }
}
