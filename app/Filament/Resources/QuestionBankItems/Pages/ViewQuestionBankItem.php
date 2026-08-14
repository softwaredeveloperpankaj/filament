<?php

namespace App\Filament\Resources\QuestionBankItems\Pages;

use App\Filament\Resources\QuestionBankItems\QuestionBankItemResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewQuestionBankItem extends ViewRecord
{
    protected static string $resource = QuestionBankItemResource::class;

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
