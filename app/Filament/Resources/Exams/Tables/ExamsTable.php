<?php

namespace App\Filament\Resources\Exams\Tables;

use App\Enums\ExamMode;
use App\Filament\Resources\Exams\ExamResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ExamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Exam Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->url(fn($record) => ExamResource::getUrl('view', ['record' => $record])),

                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->badge()
                    ->color('primary')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('branchClass.name')
                    ->label('Class')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('section.name')
                    ->label('Section')
                    ->badge()
                    ->color('success'),

                TextColumn::make('academicYear.name')
                    ->label('Academic Year')
                    ->sortable(),

                TextColumn::make('mode')
                    ->label('Mode')
                    ->badge()
                    ->icons([
                        ExamMode::ONLINE->value => 'heroicon-o-cpu-chip',
                        ExamMode::OFFLINE->value => 'heroicon-o-document-text',
                    ]),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('End Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('subjects_count')
                    ->label('Subjects')
                    ->counts('subjects')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('studentEntries_count')
                    ->label('Students')
                    ->counts('studentEntries')
                    ->badge()
                    ->color('gray'),

                IconColumn::make('is_practical')
                    ->label('Practical')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
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
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
