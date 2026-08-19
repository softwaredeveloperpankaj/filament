<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ExamSubject;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExamSubjectPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ExamSubject');
    }

    public function view(AuthUser $authUser, ExamSubject $examSubject): bool
    {
        return $authUser->can('View:ExamSubject');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ExamSubject');
    }

    public function update(AuthUser $authUser, ExamSubject $examSubject): bool
    {
        return $authUser->can('Update:ExamSubject');
    }

    public function delete(AuthUser $authUser, ExamSubject $examSubject): bool
    {
        return $authUser->can('Delete:ExamSubject');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ExamSubject');
    }

    public function restore(AuthUser $authUser, ExamSubject $examSubject): bool
    {
        return $authUser->can('Restore:ExamSubject');
    }

    public function forceDelete(AuthUser $authUser, ExamSubject $examSubject): bool
    {
        return $authUser->can('ForceDelete:ExamSubject');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ExamSubject');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ExamSubject');
    }

    public function replicate(AuthUser $authUser, ExamSubject $examSubject): bool
    {
        return $authUser->can('Replicate:ExamSubject');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ExamSubject');
    }

    public function import(AuthUser $authUser): bool
    {
        return $authUser->can('Import:ExamSubject');
    }

    public function export(AuthUser $authUser): bool
    {
        return $authUser->can('Export:ExamSubject');
    }

}