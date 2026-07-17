<?php

namespace App\Filament\Resources\FormTemplates\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FormTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->searchable()
                    ->numeric(),
                TextColumn::make('branch.name')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                // TextColumn::make('active_version_id')
                //     ->numeric()
                //     ->sortable(),
                TextColumn::make('created_by')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('registration_serial')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('type')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('status')
                    ->toggleable()
                    ->badge(),
                IconColumn::make('is_active')
                    ->toggleable()
                    ->boolean(),
                TextColumn::make('form_layout')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('rollno_generation_scope')
                    ->toggleable()
                    ->badge(),
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
                //
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
