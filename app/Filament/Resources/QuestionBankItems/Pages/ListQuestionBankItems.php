<?php

namespace App\Filament\Resources\QuestionBankItems\Pages;

use App\Filament\Resources\QuestionBankItems\QuestionBankItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQuestionBankItems extends ListRecords
{
    protected static string $resource = QuestionBankItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
