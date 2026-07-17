<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\FormTemplate;
use Illuminate\Auth\Access\HandlesAuthorization;

class FormTemplatePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:FormTemplate');
    }

    public function view(AuthUser $authUser, FormTemplate $formTemplate): bool
    {
        return $authUser->can('View:FormTemplate');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:FormTemplate');
    }

    public function update(AuthUser $authUser, FormTemplate $formTemplate): bool
    {
        return $authUser->can('Update:FormTemplate');
    }

    public function delete(AuthUser $authUser, FormTemplate $formTemplate): bool
    {
        return $authUser->can('Delete:FormTemplate');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:FormTemplate');
    }

    public function restore(AuthUser $authUser, FormTemplate $formTemplate): bool
    {
        return $authUser->can('Restore:FormTemplate');
    }

    public function forceDelete(AuthUser $authUser, FormTemplate $formTemplate): bool
    {
        return $authUser->can('ForceDelete:FormTemplate');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:FormTemplate');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:FormTemplate');
    }

    public function replicate(AuthUser $authUser, FormTemplate $formTemplate): bool
    {
        return $authUser->can('Replicate:FormTemplate');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:FormTemplate');
    }

}