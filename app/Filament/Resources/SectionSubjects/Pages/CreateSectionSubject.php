<?php

namespace App\Filament\Resources\SectionSubjects\Pages;

use App\Filament\Resources\SectionSubjects\SectionSubjectResource;
use App\Models\ClassSection;
use App\Models\SectionSubject;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Validation\ValidationException;

class CreateSectionSubject extends CreateRecord
{
    protected static string $resource = SectionSubjectResource::class;
    
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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $classSectionId = ClassSection::query()
            ->where('branch_id', $data['branch_id'])
            ->where('branch_class_id', $data['branch_class_id'])
            ->where('section_id', $data['section_id'])
            ->value('id');

        if (!$classSectionId) {
            throw ValidationException::withMessages([
                'data.section_id' => 'This class and section are not mapped. Please create the class-section first.',
            ]);
        }

        $alreadyExists = SectionSubject::query()
            ->where('branch_id', $data['branch_id'])
            ->where('branch_class_id', $data['branch_class_id'])
            ->where('section_id', $data['section_id'])
            ->where('subject_id', $data['subject_id'])
            ->exists();

        if ($alreadyExists) {
            throw ValidationException::withMessages([
                'data.subject_id' => 'This subject is already assigned to the selected class and section.',
            ]);
        }        

        $data['class_section_id'] = $classSectionId;

        return $data;
    }
}
