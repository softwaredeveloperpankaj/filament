<?php

namespace App\Filament\Resources\Imports\RelationManagers;

use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

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
                    ->getStateUsing(fn ($record) => json_encode($record->data))
                    ->limit(80)
                    ->fontFamily('mono')
                    ->action(
                        Action::make('viewData')
                            ->label('View Row Data')
                            ->modalHeading('Row Data')
                            ->modalContent(fn ($record) => new HtmlString(
                                '<pre style="white-space:pre-wrap;word-break:break-all;'
                                . 'font-family:monospace;font-size:0.85rem;padding:1rem">'
                                . e(json_encode($record->data, JSON_PRETTY_PRINT))
                                . '</pre>'
                            ))
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Close')
                    ),
                TextColumn::make('validation_error')
                    ->label('Validation Error')
                    ->color('danger')
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('At')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}