<?php

namespace App\Filament\Resources\SectionSubjects\Pages;

use App\Filament\Resources\SectionSubjects\SectionSubjectResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSectionSubject extends EditRecord
{
    protected static string $resource = SectionSubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
