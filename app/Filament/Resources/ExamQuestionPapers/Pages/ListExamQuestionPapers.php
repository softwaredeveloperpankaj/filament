<?php

namespace App\Filament\Resources\ExamQuestionPapers\Pages;

use App\Filament\Resources\ExamQuestionPapers\ExamQuestionPaperResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExamQuestionPapers extends ListRecords
{
    protected static string $resource = ExamQuestionPaperResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
