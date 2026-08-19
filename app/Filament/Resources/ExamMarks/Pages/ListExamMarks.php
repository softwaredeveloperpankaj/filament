<?php

namespace App\Filament\Resources\ExamMarks\Pages;

use App\Filament\Resources\ExamMarks\ExamMarkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExamMarks extends ListRecords
{
    protected static string $resource = ExamMarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
