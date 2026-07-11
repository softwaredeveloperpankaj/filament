<?php

namespace App\Filament\Resources\BranchClasses\Pages;

use App\Filament\Resources\BranchClasses\BranchClassResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreateBranchClass extends CreateRecord
{
    protected static string $resource = BranchClassResource::class;

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->hidden(true);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back')
                ->url($this->getResource()::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
        ];
    }    
}
