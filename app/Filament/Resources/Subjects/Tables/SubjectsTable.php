<?php

namespace App\Filament\Resources\Subjects\Tables;

use App\Filament\Exports\SubjectExporter;
use App\Filament\Imports\SubjectImporter;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->numeric()
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->toggleable(),
                TextColumn::make('name')
                    ->label('Subject Name')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('code')
                    ->label('Subject Code')
                    ->numeric()
                    ->toggleable()
                    ->sortable(),
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
                    ExportBulkAction::make()
                        ->exporter(SubjectExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(SubjectImporter::class)
            ]);
    }
}
