<?php

namespace App\Filament\Resources\FormTemplates\Pages;

use App\Filament\Resources\FormTemplates\FormTemplateResource;
use App\Models\Branch;
use App\Models\FormTemplate;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateFormTemplate extends CreateRecord
{
    protected static string $resource = FormTemplateResource::class;
    
    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->hidden(true);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back')
                ->url($this->getResource()::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
        ];
    }
    
    protected function handleRecordCreation(array $data): Model
    {
        try {
            return parent::handleRecordCreation($data);

        } catch (\Exception $e) {

            Notification::make()
                ->danger()
                ->title('System Error')
                ->body('Something went wrong while processing your request. Please try again or contact support if the issue persists.')
                ->persistent()
                ->send();

            $this->halt();
            return new FormTemplate();
        }
    }    
}
