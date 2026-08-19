<?php

namespace App\Filament\Resources\ExamMarks\Pages;

use App\Filament\Resources\ExamMarks\ExamMarkResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewExamMark extends ViewRecord
{
    protected static string $resource = ExamMarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
