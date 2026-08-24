<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\TeacherProfile;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

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

    // protected function handleRecordCreation(array $data): Model
    // {
    //     return DB::transaction(function () use ($data) {
    //         $roles = $data['roles'] ?? [];
    //         $teacherProfileData = $data['teacherProfile'] ?? null;
    //         unset($data['roles'], $data['teacherProfile']);
    //         $user = User::create($data);

    //         if (! empty($roles)) {
    //             $user->syncRoles($roles);
    //         }
    //         $user->refresh();
    //         if ($user->hasRole('teacher')) {
    //             if (! $teacherProfileData) {
    //                 throw new \Exception('Teacher profile data is required.');
    //             }

    //             $nextEmployeeId = (TeacherProfile::max('employee_id') ?? 0) + 1;
    //             $teacherProfileData['employee_id'] = $nextEmployeeId;
    //             try {
    //                 $user->teacherProfile()->create($teacherProfileData);
    //             } catch (\Throwable $th) {
    //                 throw new \Exception('Something went wrong while creating the teacher profile.'.$th->getMessage());
    //             }
    //         }

    //         return $user;
    //     });
    // }    

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $roles            = $data['roles'] ?? [];
            $teacherProfileData = $data['teacherProfile'] ?? null;
            $subjectIds       = $teacherProfileData['subjects'] ?? [];
            unset($data['roles'], $data['teacherProfile'], $teacherProfileData['subjects']);

            // Determine primary role for employee ID
            $primaryRole = Role::whereIn('id', $roles)->first();

            // Branch from teacher profile
            $branchId = $teacherProfileData['branch_id'] ?? null;

            if ($primaryRole && $branchId) {
                $data['employee_id'] = User::generateEmployeeId($primaryRole->name, $branchId);
                $data['branch_id']   = $branchId;
            }

            $user = User::create($data);
            if (! empty($roles)) {
                $user->syncRoles($roles);
            }
            $user->refresh();

            if ($user->hasRole('teacher') && $teacherProfileData) {
                $profile = $user->teacherProfile()->create($teacherProfileData);
                if (! empty($subjectIds)) {
                    $profile->subjects()->sync($subjectIds);
                }
            }

            return $user;
        });
    }

}
