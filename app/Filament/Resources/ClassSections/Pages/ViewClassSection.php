<?php

namespace App\Filament\Resources\ClassSections\Pages;

use App\Filament\Resources\ClassSections\ClassSectionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewClassSection extends ViewRecord
{
    protected static string $resource = ClassSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
