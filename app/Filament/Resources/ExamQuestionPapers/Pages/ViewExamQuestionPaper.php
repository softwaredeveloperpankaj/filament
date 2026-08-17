<?php

namespace App\Filament\Resources\ExamQuestionPapers\Pages;

use App\Filament\Resources\ExamQuestionPapers\ExamQuestionPaperResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewExamQuestionPaper extends ViewRecord
{
    protected static string $resource = ExamQuestionPaperResource::class;

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
