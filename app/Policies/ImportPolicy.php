<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Import;
use Illuminate\Auth\Access\HandlesAuthorization;

class ImportPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Import');
    }

    public function view(AuthUser $authUser, Import $import): bool
    {
        return $authUser->can('View:Import');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Import');
    }

    public function update(AuthUser $authUser, Import $import): bool
    {
        return $authUser->can('Update:Import');
    }

    public function delete(AuthUser $authUser, Import $import): bool
    {
        return $authUser->can('Delete:Import');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Import');
    }

    public function restore(AuthUser $authUser, Import $import): bool
    {
        return $authUser->can('Restore:Import');
    }

    public function forceDelete(AuthUser $authUser, Import $import): bool
    {
        return $authUser->can('ForceDelete:Import');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Import');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Import');
    }

    public function replicate(AuthUser $authUser, Import $import): bool
    {
        return $authUser->can('Replicate:Import');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Import');
    }

    public function import(AuthUser $authUser): bool
    {
        return $authUser->can('Import:Import');
    }

    public function export(AuthUser $authUser): bool
    {
        return $authUser->can('Export:Import');
    }

}