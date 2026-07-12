<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
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
        }

        return $data;
    }    

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $roles = $data['roles'] ?? [];
            $teacherProfileData = $data['teacherProfile'] ?? null;

            unset($data['roles'], $data['teacherProfile']);

            $record->update($data);

            $record->roles()->sync($roles);

            if ($record->hasRole('teacher')) {
                if (! $teacherProfileData) {
                    throw new \Exception('Teacher profile data is required.');
                }

                $record->teacherProfile()->updateOrCreate(
                    ['user_id' => $record->id],
                    $teacherProfileData
                );
            }

            return $record;
        });
    }   
}
