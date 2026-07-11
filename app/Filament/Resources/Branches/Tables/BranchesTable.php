<?php

namespace App\Filament\Resources\Branches\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->numeric()
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('school.name')
                    ->label('School')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('name')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('code')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('address')
                    ->toggleable(),
                TextColumn::make('phone')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('is_main')
                    ->toggleable()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No'),
                TextColumn::make('is_active')
                    ->toggleable()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('is_main')
                    ->query(fn ($query) => $query->where('is_main', true))
                    ->label('Main Branch'),
                Filter::make('is_active')
                    ->query(fn ($query) => $query->where('is_active', true))
                    ->label('Active Branch'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->recordActionsColumnLabel('Actions')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
