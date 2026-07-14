<?php

namespace App\Filament\Resources\SectionSubjects\Pages;

use App\Filament\Resources\SectionSubjects\SectionSubjectResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSectionSubject extends ViewRecord
{
    protected static string $resource = SectionSubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
