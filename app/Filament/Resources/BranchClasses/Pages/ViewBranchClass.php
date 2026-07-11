<?php

namespace App\Filament\Resources\BranchClasses\Pages;

use App\Filament\Resources\BranchClasses\BranchClassResource;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBranchClass extends ViewRecord
{
    protected static string $resource = BranchClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back')
                ->url($this->getResource()::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
