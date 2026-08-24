<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

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
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $profile = $this->record->teacherProfile;

        if ($profile) {
            $data['teacherProfile'] = $profile->toArray();
            $data['teacherProfile']['subjects'] = $profile->subjects()->pluck('subjects.id')->toArray();
        }

        $data['employee_id'] = $this->record->employee_id;

        return $data;
    }  

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $roles = $data['roles'] ?? [];
            $teacherProfileData = $data['teacherProfile'] ?? null;

            $subjectIds = $teacherProfileData['subjects'] ?? [];

            if ($teacherProfileData) {
                unset($teacherProfileData['subjects']);
            }

            unset($data['roles'], $data['teacherProfile']);

            if (! empty($teacherProfileData['branch_id'])) {
                $data['branch_id'] = $teacherProfileData['branch_id'];
            }

            unset($data['employee_id']);

            $record->update($data);

            $record->roles()->sync($roles);
            $record->refresh();

            if ($record->hasRole('teacher')) {
                if (! $teacherProfileData) {
                    Notification::make()
                        ->title('Error while creating teach profile!!')
                        ->body('Teacher profile data is required.')
                        ->danger()
                        ->send();
                }

                $profile = $record->teacherProfile()->updateOrCreate(
                    ['user_id' => $record->id],
                    $teacherProfileData
                );

                $profile->subjects()->sync($subjectIds);
            } else {
                if(!empty($teacherProfileData)){
                    $record->teacherProfile()?->subjects()->detach();
                    $record->teacherProfile()?->delete();
                }
            }

            return $record;
        });
    } 
}
