<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

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

            Action::make('printAdmissionForm')
                ->label('Print Admission Form')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->url(fn ($record) => route('students.admission-form-print', $record))
                ->openUrlInNewTab(),            

            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
