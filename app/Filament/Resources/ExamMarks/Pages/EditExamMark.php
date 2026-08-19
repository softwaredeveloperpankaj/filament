<?php

namespace App\Filament\Resources\ExamMarks\Pages;

use App\Filament\Resources\ExamMarks\ExamMarkResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditExamMark extends EditRecord
{
    protected static string $resource = ExamMarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
