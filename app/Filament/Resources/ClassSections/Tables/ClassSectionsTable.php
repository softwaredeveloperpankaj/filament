<?php

namespace App\Filament\Resources\ClassSections\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClassSectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('branchClass.name')
                    ->label('Class')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('section.name')
                    ->label('Section')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('section.starting_roll_no')
                    ->label('Starting Roll No')
                    ->sortable(),

                TextColumn::make('section_subjects_count')
                    ->label('Subjects')
                    ->counts('sectionSubjects')     // counts related subjects
                    ->badge()
                    ->color('primary'),

                TextColumn::make('created_at')
                    ->label('Assigned On')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->relationship('branch', 'name'),

                SelectFilter::make('branch_class_id')
                    ->label('Class')
                    ->relationship('branchClass', 'name'),

                SelectFilter::make('section_id')
                    ->label('Section')
                    ->relationship('section', 'name'),
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
