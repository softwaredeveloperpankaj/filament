<?php

namespace App\Filament\Resources\SectionSubjects\Pages;

use App\Filament\Resources\SectionSubjects\SectionSubjectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSectionSubjects extends ListRecords
{
    protected static string $resource = SectionSubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
