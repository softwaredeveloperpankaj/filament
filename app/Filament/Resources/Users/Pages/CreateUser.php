<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\TeacherProfile;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateUser extends CreateRecord
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
        ];
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $roles = $data['roles'] ?? [];
            $teacherProfileData = $data['teacherProfile'] ?? null;
            unset($data['roles'], $data['teacherProfile']);
            $user = User::create($data);

            if (! empty($roles)) {
                $user->syncRoles($roles);
            }
            $user->refresh();
            if ($user->hasRole('teacher')) {
                if (! $teacherProfileData) {
                    throw new \Exception('Teacher profile data is required.');
                }

                $nextEmployeeId = (TeacherProfile::max('employee_id') ?? 0) + 1;
                $teacherProfileData['employee_id'] = $nextEmployeeId;
                try {
                    $user->teacherProfile()->create($teacherProfileData);
                } catch (\Throwable $th) {
                    throw new \Exception('Something went wrong while creating the teacher profile.');
                }
            }

            return $user;
        });
    }    
}
