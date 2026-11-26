<?php

namespace App\Filament\Resources\Topics\Schemas;

use App\Models\Subject;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
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
                Section::make('Topic Assignment')
                    ->description('Choose the branch and subject for this topic.')
                    ->icon('heroicon-o-bookmark')
                    ->schema([Grid::make(['default' => 1, 'sm' => 2])->schema([
                        Select::make('branch_id')->label('Branch')->relationship('branch', 'name')->searchable()->preload()->required()->live()
                            ->afterStateUpdated(fn($state, callable $set) => $set('subject_id', null)),
                        Select::make('subject_id')->relationship('subject', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->code})")
                            ->searchable()->preload()->required()
                            ->disabled(fn(Get $get) => !$get('branch_id') && !Auth::user()?->branch_id),
                    ])])->collapsible()->columnSpanFull(),
                Section::make('Topic Information')
                    ->description('Define the topic name, URL slug, description, and display order.')
                    ->icon('heroicon-o-document-text')
                    ->schema([Grid::make(['default' => 1, 'sm' => 2])->schema([
                        TextInput::make('name')->label('Topic / Chapter Name')->placeholder('e.g. Quadratic Equations, Newton\'s Laws of Motion')->required()->maxLength(150)->live(onBlur: true)
                            ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')->label('Slug')->required()->maxLength(160)->unique(ignoreRecord: true)->helperText('Auto-generated from name.'),
                        Textarea::make('description')->label('Description')->rows(3)->columnSpanFull()->placeholder('Brief overview of what this topic covers...'),
                        TextInput::make('order')->label('Display Order')->numeric()->default(0)->helperText('Controls the sequence topics appear in (e.g., Chapter 1, 2, 3...).'),
                        Toggle::make('is_active')->label('Active')->default(true)->helperText('Inactive topics are hidden from question creation dropdowns.')->columnSpanFull(),
                    ])])->collapsible()->columnSpanFull(),
            ]);
    }
}
