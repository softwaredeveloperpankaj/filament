<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Branch Identity')
                    ->description('Configure the school, administrator, and branch identity.')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([Grid::make(['default' => 1, 'sm' => 2])->schema([
                        Select::make('school_id')->relationship('school', 'name')->searchable()->preload()->required()->placeholder('Select a school'),
                        Select::make('user_id')->label('User')->relationship(name: 'user', titleAttribute: 'name', modifyQueryUsing: fn ($query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'admin')))->searchable()->preload()->placeholder('Select a user')->required(),
                        TextInput::make('name')->label('Branch name')->placeholder('Branch name')->required(),
                        TextInput::make('code')->label('Branch code')->placeholder('Branch code'),
                    ])])->collapsible()->columnSpanFull(),
                Section::make('Contact & Status')
                    ->description('Maintain contact information and operational status.')
                    ->icon('heroicon-o-phone')
                    ->schema([Grid::make(['default' => 1, 'sm' => 2])->schema([
                        TextInput::make('email')->label('Email address')->placeholder('Email address')->email(),
                        TextInput::make('phone')->label('Phone')->placeholder('9876543210')->tel(),
                        Textarea::make('address')->label('Address')->placeholder('Address')->columnSpanFull(),
                        Toggle::make('is_main')->onIcon('heroicon-o-check-circle')->offIcon('heroicon-o-x-circle'),
                        Toggle::make('is_active')->onIcon('heroicon-o-check-circle')->offIcon('heroicon-o-x-circle'),
                    ])])->collapsible()->columnSpanFull(),
            ]);
    }
}
