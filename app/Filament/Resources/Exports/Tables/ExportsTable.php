<?php

namespace App\Filament\Resources\Exports\Tables;

use App\Models\Export;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

class ExportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable()->width('60px'),

                TextColumn::make('exporter')
                    ->label('Resource')
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->badge()->color('info')
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Exported By')->searchable()->sortable(),

                TextColumn::make('file_name')
                    ->label('File')->searchable()->limit(30)
                    ->tooltip(fn (Export $record) => $record->file_name),

                TextColumn::make('total_rows')->label('Total')->numeric()->alignCenter(),
                TextColumn::make('processed_rows')->label('Processed')->numeric()->alignCenter(),
                TextColumn::make('successful_rows')->label('Successful')->numeric()->alignCenter()->color('success'),

                TextColumn::make('failed_rows_count')
                    ->label('Failed')
                    ->getStateUsing(fn (Export $export) => $export->total_rows - $export->successful_rows)
                    ->alignCenter()
                    ->color(fn ($status) => $status > 0 ? 'danger' : 'success'),

                TextColumn::make('status')
                    ->label('Status')->badge()
                    ->getStateUsing(fn (Export $export) => $export->status),
                    // ->color(fn ($state) => match($state) {
                    //     'Completed' => 'success',
                    //     'In Progress' => 'warning',
                    //     default => 'gray',
                    // }),

                TextColumn::make('duration')
                    ->label('Duration')
                    ->getStateUsing(fn (Export $export) => $export->duration ?? '—')
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
                SelectFilter::make('exporter')->label('Resource')
                    ->options(
                        Export::query()->distinct()->pluck('exporter','exporter')
                            ->mapWithKeys(fn ($exp) => [$exp => class_basename($exp)])->toArray()
                    ),
                SelectFilter::make('user_id')->label('User')->relationship('user', 'name'),
                Filter::make('has_failures')->label('Has Failed Rows')
                    ->query(fn (Builder $query) => $query->whereRaw('total_rows - successful_rows > 0')),
                Filter::make('completed')->label('Completed Only')
                    ->query(fn (Builder $query) => $query->whereNotNull('completed_at')),
                Filter::make('in_progress')->label('In Progress')
                    ->query(fn (Builder $query) => $query->whereNull('completed_at')),
            ])
            ->recordActions([
                ViewAction::make(),   // ← add this
                ActionGroup::make([
                    Action::make('download')
                        ->label('Download')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(fn (Export $record) => response()->streamDownload(
                            fn () => print(Storage::disk($record->file_disk)->get($record->file_path)),
                            basename($record->file_path)
                        ))
                        ->visible(fn (Export $record): bool =>
                            ! is_null($record->file_path) && ! is_null($record->completed_at)
                        ),
                ]),
            ]);
    }
}
