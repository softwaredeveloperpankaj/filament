<?php

namespace App\Filament\Resources\Subjects\Pages;

use App\Filament\Resources\Subjects\SubjectResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateSubject extends CreateRecord
{
    protected static string $resource = SubjectResource::class;
    
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
