<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\Schemas\StudentForm;
use App\Filament\Resources\Students\StudentResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

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

    public function mount(): void
    {
        parent::mount();

        $templateId = $this->data['form_template_id'] ?? null;

        $this->form->fill([
            'form_data' => StudentForm::mergeDynamicFormDefaults(
                $this->data['form_data'] ?? [],
                $templateId
            ),
        ]);
    }
}
