<?php

namespace App\Filament\Resources\ExamQuestionPapers\Pages;

use App\Filament\Resources\ExamQuestionPapers\ExamQuestionPaperResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateExamQuestionPaper extends CreateRecord
{
    protected static string $resource = ExamQuestionPaperResource::class;

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
