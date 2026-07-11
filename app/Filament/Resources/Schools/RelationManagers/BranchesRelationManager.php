<?php

namespace App\Filament\Resources\Schools\RelationManagers;

use App\Filament\Resources\BranchClasses\BranchClassResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BranchesRelationManager extends RelationManager
{
    protected static string $relationship = 'branches';

    protected static ?string $relatedResource = BranchClassResource::class;

    public function table(Table $table): Table
    {
        // return $table
        //     ->headerActions([
        //         CreateAction::make(),
        //     ]);

        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Branch Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Assigned Admin')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('code'),
                
                IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                    BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);        
    }
}
