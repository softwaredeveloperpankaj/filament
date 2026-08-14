<?php

namespace App\Filament\Resources\Topics\Schemas;

use App\Models\Subject;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TopicForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('branch_id')
                    ->label('Branch')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn($state, callable $set) => $set('subject_id', null)),

                // Subject (scoped to branch)
                Select::make('subject_id')
                    ->label('Subject')
                    ->options(fn(Get $get) => Subject::query()
                        ->where('branch_id', $get('branch_id') ?? Auth::user()?->branch_id)
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(fn(Get $get) => !$get('branch_id') && !Auth::user()?->branch_id),

                // Name
                TextInput::make('name')
                    ->label('Topic / Chapter Name')
                    ->placeholder('e.g. Quadratic Equations, Newton\'s Laws of Motion')
                    ->required()
                    ->maxLength(150)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),

                // Slug
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(160)
                    ->unique(ignoreRecord: true)
                    ->helperText('Auto-generated from name.'),

                // Description
                Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->columnSpanFull()
                    ->placeholder('Brief overview of what this topic covers...'),

                // Order
                TextInput::make('order')
                    ->label('Display Order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Controls the sequence topics appear in (e.g., Chapter 1, 2, 3...).'),

                // Status
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Inactive topics are hidden from question creation dropdowns.'),
            ]);
    }
}
