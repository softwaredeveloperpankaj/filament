<?php

namespace App\Filament\Resources\BranchClasses\RelationManagers;

use App\Filament\Resources\BranchClasses\BranchClassResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class ClassSectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'classSections';

    protected static ?string $relatedResource = BranchClassResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
