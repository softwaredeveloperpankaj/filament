<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Export;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExportPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Export');
    }

    public function view(AuthUser $authUser, Export $export): bool
    {
        return $authUser->can('View:Export');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Export');
    }

    public function update(AuthUser $authUser, Export $export): bool
    {
        return $authUser->can('Update:Export');
    }

    public function delete(AuthUser $authUser, Export $export): bool
    {
        return $authUser->can('Delete:Export');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Export');
    }

    public function restore(AuthUser $authUser, Export $export): bool
    {
        return $authUser->can('Restore:Export');
    }

    public function forceDelete(AuthUser $authUser, Export $export): bool
    {
        return $authUser->can('ForceDelete:Export');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Export');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Export');
    }

    public function replicate(AuthUser $authUser, Export $export): bool
    {
        return $authUser->can('Replicate:Export');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Export');
    }

    public function import(AuthUser $authUser): bool
    {
        return $authUser->can('Import:Export');
    }

    public function export(AuthUser $authUser): bool
    {
        return $authUser->can('Export:Export');
    }

}