<?php

namespace App\Filament\Resources\ExamSchedules\Tables;

use App\Models\Exam;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExamSchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('exam_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('start_time')
                    ->label('Time')
                    ->formatStateUsing(fn($state, $record) => $state?->format('H:i') . ' – ' . $record->end_time?->format('H:i'))
                    ->sortable(),

                TextColumn::make('exam.name')
                    ->label('Exam')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('examSubject.subject.name')
                    ->label('Subject')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('room_number')
                    ->label('Room')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                TextColumn::make('invigilator.name')
                    ->label('Invigilator')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('examSubject.is_graded')
                    ->label('Graded')
                    ->boolean(),
            ])
            ->defaultSort('exam_date')
            ->filters([
                SelectFilter::make('exam_subject_id')
                    ->label('Subject')
                    ->relationship('examSubject.subject', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Schedule')
                    ->icon('heroicon-o-plus')
                    ->color('primary'),
            ])
            ->recordActionsColumnLabel('Actions')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->icon('heroicon-o-eye')->color('info'),
                    EditAction::make()->icon('heroicon-o-pencil')->color('primary'),
                    DeleteAction::make()->icon('heroicon-o-trash')->color('danger')->requiresConfirmation(),
                ])->dropdownPlacement('bottom-start'),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
