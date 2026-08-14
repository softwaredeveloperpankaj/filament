<?php

namespace App\Filament\Resources\QuestionBankItems\Pages;

use App\Filament\Resources\QuestionBankItems\QuestionBankItemResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateQuestionBankItem extends CreateRecord
{
    protected static string $resource = QuestionBankItemResource::class;

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
