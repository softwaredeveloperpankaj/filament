<?php

namespace App\Filament\Resources\ExamSubjects\Pages;

use App\Filament\Resources\ExamSubjects\ExamSubjectResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\CreateRecord;

class CreateExamSubject extends CreateRecord
{
    protected static string $resource = ExamSubjectResource::class;

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
