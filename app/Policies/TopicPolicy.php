<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Topic;
use Illuminate\Auth\Access\HandlesAuthorization;

class TopicPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Topic');
    }

    public function view(AuthUser $authUser, Topic $topic): bool
    {
        return $authUser->can('View:Topic');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Topic');
    }

    public function update(AuthUser $authUser, Topic $topic): bool
    {
        return $authUser->can('Update:Topic');
    }

    public function delete(AuthUser $authUser, Topic $topic): bool
    {
        return $authUser->can('Delete:Topic');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Topic');
    }

    public function restore(AuthUser $authUser, Topic $topic): bool
    {
        return $authUser->can('Restore:Topic');
    }

    public function forceDelete(AuthUser $authUser, Topic $topic): bool
    {
        return $authUser->can('ForceDelete:Topic');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Topic');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Topic');
    }

    public function replicate(AuthUser $authUser, Topic $topic): bool
    {
        return $authUser->can('Replicate:Topic');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Topic');
    }

}