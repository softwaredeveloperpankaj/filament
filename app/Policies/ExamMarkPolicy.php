<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ExamMark;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExamMarkPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ExamMark');
    }

    public function view(AuthUser $authUser, ExamMark $examMark): bool
    {
        return $authUser->can('View:ExamMark');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ExamMark');
    }

    public function update(AuthUser $authUser, ExamMark $examMark): bool
    {
        return $authUser->can('Update:ExamMark');
    }

    public function delete(AuthUser $authUser, ExamMark $examMark): bool
    {
        return $authUser->can('Delete:ExamMark');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ExamMark');
    }

    public function restore(AuthUser $authUser, ExamMark $examMark): bool
    {
        return $authUser->can('Restore:ExamMark');
    }

    public function forceDelete(AuthUser $authUser, ExamMark $examMark): bool
    {
        return $authUser->can('ForceDelete:ExamMark');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ExamMark');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ExamMark');
    }

    public function replicate(AuthUser $authUser, ExamMark $examMark): bool
    {
        return $authUser->can('Replicate:ExamMark');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ExamMark');
    }

    public function import(AuthUser $authUser): bool
    {
        return $authUser->can('Import:ExamMark');
    }

    public function export(AuthUser $authUser): bool
    {
        return $authUser->can('Export:ExamMark');
    }

}