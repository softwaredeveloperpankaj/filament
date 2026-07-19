<?php

namespace App\Filament\Resources\FormTemplates\Pages;

use App\Filament\Resources\FormTemplates\FormTemplateResource;
use App\Models\Branch;
use App\Models\FormTemplate;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;

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

        } catch (UniqueConstraintViolationException $e) {
            // Find which branch already has a template
            $branchName = Branch::find($data['branch_id'])?->name ?? 'the selected branch';

            Notification::make()
                ->danger()
                ->title('Duplicate Template')
                ->body("A form template already exists for \"{$branchName}\". Each branch can only have one active template. Please edit the existing one instead.")
                ->persistent()
                ->send();

            $this->halt();
            return new FormTemplate();
        }
    }    
}
