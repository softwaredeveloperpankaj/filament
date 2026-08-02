<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back')
                ->url($this->getResource()::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),

            Action::make('print_admission')
                ->label('Print Admission Form')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn () => StudentResource::getUrl('print', ['record' => $this->record]))
                ->openUrlInNewTab(),                

            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
