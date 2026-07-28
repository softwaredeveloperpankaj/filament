<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    public function mount(): void
    {
        parent::mount();

        // Pre-initialize form_data so Livewire tracks it on first page load
        $this->form->fill([
            'form_data' => [],
        ]);
    }    
}
