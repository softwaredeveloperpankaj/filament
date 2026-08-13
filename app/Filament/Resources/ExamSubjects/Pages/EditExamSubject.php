<?php

namespace App\Filament\Resources\ExamSubjects\Pages;

use App\Filament\Resources\ExamSubjects\ExamSubjectResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditExamSubject extends EditRecord
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
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
