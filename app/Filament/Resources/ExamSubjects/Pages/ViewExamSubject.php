<?php

namespace App\Filament\Resources\ExamSubjects\Pages;

use App\Filament\Resources\ExamSubjects\ExamSubjectResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewExamSubject extends ViewRecord
{
    protected static string $resource = ExamSubjectResource::class;

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
