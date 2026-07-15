<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BranchClass;
use Illuminate\Auth\Access\HandlesAuthorization;

class BranchClassPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BranchClass');
    }

    public function view(AuthUser $authUser, BranchClass $branchClass): bool
    {
        return $authUser->can('View:BranchClass');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BranchClass');
    }

    public function update(AuthUser $authUser, BranchClass $branchClass): bool
    {
        return $authUser->can('Update:BranchClass');
    }

    public function delete(AuthUser $authUser, BranchClass $branchClass): bool
    {
        return $authUser->can('Delete:BranchClass');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:BranchClass');
    }

    public function restore(AuthUser $authUser, BranchClass $branchClass): bool
    {
        return $authUser->can('Restore:BranchClass');
    }

    public function forceDelete(AuthUser $authUser, BranchClass $branchClass): bool
    {
        return $authUser->can('ForceDelete:BranchClass');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BranchClass');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BranchClass');
    }

    public function replicate(AuthUser $authUser, BranchClass $branchClass): bool
    {
        return $authUser->can('Replicate:BranchClass');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BranchClass');
    }

}