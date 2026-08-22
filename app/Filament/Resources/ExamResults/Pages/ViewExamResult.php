<?php

namespace App\Filament\Resources\ExamResults\Pages;

use App\Filament\Resources\ExamResults\ExamResultResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewExamResult extends ViewRecord
{
    protected static string $resource = ExamResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back')
                ->url($this->getResource()::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
            // EditAction::make(),
            // DeleteAction::make(),
        ];
    }
}
