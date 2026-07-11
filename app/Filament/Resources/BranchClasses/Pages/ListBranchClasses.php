<?php

namespace App\Filament\Resources\BranchClasses\Pages;

use App\Filament\Resources\BranchClasses\BranchClassResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBranchClasses extends ListRecords
{
    protected static string $resource = BranchClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
