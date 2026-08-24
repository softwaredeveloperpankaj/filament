<?php

namespace App\Filament\Resources\Subjects\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SectionSubjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'sectionSubjects';

    protected static ?string $title = 'Class Section Assignments';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable(),

                Tables\Columns\TextColumn::make('branchClass.name')
                    ->label('Class')
                    ->searchable(),

                Tables\Columns\TextColumn::make('section.name')
                    ->label('Section')
                    ->searchable(),

                Tables\Columns\TextColumn::make('teacher.user.name')
                    ->label('Teacher')
                    ->placeholder('Not assigned')
                    ->searchable(),

                Tables\Columns\TextColumn::make('teacher.user.employee_id')
                    ->label('Employee ID')
                    ->placeholder('—'),
            ]);
            // ->headerActions([
            //     CreateAction::make()
            //         ->url(fn (): string => route(
            //             'filament.admin.resources.section-subjects.create',
            //             ['subject_id' => $this->getOwnerRecord()->getKey()],
            //         )),
            // ])
            // ->recordActions([
            //     ViewAction::make(),
            //     EditAction::make(),
            // ]);
    }
}