<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OnlineExamAttempt;
use Illuminate\Auth\Access\HandlesAuthorization;

class OnlineExamAttemptPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OnlineExamAttempt');
    }

    public function view(AuthUser $authUser, OnlineExamAttempt $onlineExamAttempt): bool
    {
        return $authUser->can('View:OnlineExamAttempt');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OnlineExamAttempt');
    }

    public function update(AuthUser $authUser, OnlineExamAttempt $onlineExamAttempt): bool
    {
        return $authUser->can('Update:OnlineExamAttempt');
    }

    public function delete(AuthUser $authUser, OnlineExamAttempt $onlineExamAttempt): bool
    {
        return $authUser->can('Delete:OnlineExamAttempt');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:OnlineExamAttempt');
    }

    public function restore(AuthUser $authUser, OnlineExamAttempt $onlineExamAttempt): bool
    {
        return $authUser->can('Restore:OnlineExamAttempt');
    }

    public function forceDelete(AuthUser $authUser, OnlineExamAttempt $onlineExamAttempt): bool
    {
        return $authUser->can('ForceDelete:OnlineExamAttempt');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OnlineExamAttempt');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OnlineExamAttempt');
    }

    public function replicate(AuthUser $authUser, OnlineExamAttempt $onlineExamAttempt): bool
    {
        return $authUser->can('Replicate:OnlineExamAttempt');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OnlineExamAttempt');
    }

    public function import(AuthUser $authUser): bool
    {
        return $authUser->can('Import:OnlineExamAttempt');
    }

    public function export(AuthUser $authUser): bool
    {
        return $authUser->can('Export:OnlineExamAttempt');
    }

}