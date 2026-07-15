<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ClassSection;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClassSectionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ClassSection');
    }

    public function view(AuthUser $authUser, ClassSection $classSection): bool
    {
        return $authUser->can('View:ClassSection');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ClassSection');
    }

    public function update(AuthUser $authUser, ClassSection $classSection): bool
    {
        return $authUser->can('Update:ClassSection');
    }

    public function delete(AuthUser $authUser, ClassSection $classSection): bool
    {
        return $authUser->can('Delete:ClassSection');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ClassSection');
    }

    public function restore(AuthUser $authUser, ClassSection $classSection): bool
    {
        return $authUser->can('Restore:ClassSection');
    }

    public function forceDelete(AuthUser $authUser, ClassSection $classSection): bool
    {
        return $authUser->can('ForceDelete:ClassSection');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ClassSection');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ClassSection');
    }

    public function replicate(AuthUser $authUser, ClassSection $classSection): bool
    {
        return $authUser->can('Replicate:ClassSection');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ClassSection');
    }

}