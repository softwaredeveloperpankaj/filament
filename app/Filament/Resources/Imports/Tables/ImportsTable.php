<?php

namespace App\Filament\Resources\Imports\Tables;

use App\Models\Import;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ImportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable()->width('60px'),

                TextColumn::make('importer')
                    ->label('Resource')
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->badge()->color('info')
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Imported By')->searchable()->sortable(),

                TextColumn::make('file_name')
                    ->label('File')->searchable()->limit(30)
                    ->tooltip(fn (Import $record) => $record->file_name),

                TextColumn::make('total_rows')->label('Total')->numeric()->alignCenter(),
                TextColumn::make('processed_rows')->label('Processed')->numeric()->alignCenter(),
                TextColumn::make('successful_rows')->label('Successful')->numeric()->alignCenter()->color('success'),

                TextColumn::make('failed_rows_count')
                    ->label('Failed')
                    ->getStateUsing(fn (Import $record) => $record->total_rows - $record->successful_rows)
                    ->alignCenter()
                    ->color(fn (int $state) => $state > 0 ? 'danger' : 'success'),

                TextColumn::make('status')
                    ->label('Status')->badge()
                    ->getStateUsing(fn (Import $record) => $record->status)
                    ->color(fn (string $state) => match($state) {
                        'Completed' => 'success',
                        'In Progress' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('duration')
                    ->label('Duration')
                    ->getStateUsing(fn (Import $record) => $record->duration ?? '—')
                    ->alignCenter(),

                TextColumn::make('completed_at')
                    ->label('Completed At')->dateTime('d M Y, h:i A')
                    ->sortable()->placeholder('Pending'),

                TextColumn::make('created_at')
                    ->label('Started At')->dateTime('d M Y, h:i A')
                    ->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('importer')->label('Resource')
                    ->options(
                        Import::query()->distinct()->pluck('importer','importer')
                            ->mapWithKeys(fn ($v) => [$v => class_basename($v)])->toArray()
                    ),
                SelectFilter::make('user_id')->label('User')->relationship('user', 'name'),
                Filter::make('has_failures')->label('Has Failed Rows')
                    ->query(fn (Builder $q) => $q->whereRaw('total_rows - successful_rows > 0')),
                Filter::make('completed')->label('Completed Only')
                    ->query(fn (Builder $q) => $q->whereNotNull('completed_at')),
                Filter::make('in_progress')->label('In Progress')
                    ->query(fn (Builder $q) => $q->whereNull('completed_at')),
            ]);
    }
}
