<?php

namespace App\Filament\Resources\Imports\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FailedRowsRelationManager extends RelationManager
{
    protected static string $relationship = 'failedRows';
    protected static ?string $title = 'Failed Rows';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->width('60px'),
                TextColumn::make('data')
                    ->label('Row Data')
                    ->getStateUsing(fn ($r) => json_encode($r->data))
                    ->limit(80)->tooltip(fn ($r) => json_encode($r->data, JSON_PRETTY_PRINT))
                    ->fontFamily('mono'),
                TextColumn::make('validation_error')
                    ->label('Validation Error')->color('danger')->wrap(),
                TextColumn::make('created_at')
                    ->label('At')->dateTime('d M Y, h:i A')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([])->bulkActions([]);
    }
}