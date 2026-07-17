<?php

namespace App\Filament\Resources\SectionSubjects\Pages;

use App\Filament\Resources\SectionSubjects\SectionSubjectResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSectionSubject extends ViewRecord
{
    protected static string $resource = SectionSubjectResource::class;

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
