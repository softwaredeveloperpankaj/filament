<?php

namespace App\Filament\Resources\ExamSubjects\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExamSubjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('exam.name')
                    ->searchable(),
                TextColumn::make('subject_class_section')
                    ->label('Subject - Class - Section')
                    ->state(function ($record): string {
                        return collect([
                            $record->subject?->name,
                            $record->branchClass?->name,
                            $record->section?->name,
                        ])
                            ->filter()
                            ->implode(' - ');
                    })
                    ->searchable([
                        'subject.name',
                        'branchClass.name',
                        'section.name',
                    ]),
                TextColumn::make('max_marks')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pass_marks')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('theory_marks')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('practical_marks')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('duration_minutes')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_graded')
                    ->boolean(),
                TextColumn::make('display_order')
                    ->numeric()
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
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
