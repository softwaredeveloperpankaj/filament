<?php

namespace App\Filament\Resources\Exams\Tables;

use App\Enums\ExamMode;
use App\Enums\ExamStatus;
use App\Filament\Resources\Exams\ExamResource;
use Filament\Actions\BulkActionGroup;
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
                    ->colors([
                        'info' => ExamMode::ONLINE->value,
                        'success' => ExamMode::OFFLINE->value,
                    ])
                    ->icons([
                        ExamMode::ONLINE->value => 'heroicon-o-cpu-chip',
                        ExamMode::OFFLINE->value => 'heroicon-o-document-text',
                    ]),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'gray' => ExamStatus::DRAFT->value,
                        'info' => ExamStatus::SCHEDULED->value,
                        'warning' => ExamStatus::ONGOING->value,
                        'success' => ExamStatus::COMPLETED->value,
                        'primary' => ExamStatus::RESULTS_PUBLISHED->value,
                        'danger' => ExamStatus::CANCELLED->value,
                    ])
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

                TextColumn::make('entries_count')
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
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
