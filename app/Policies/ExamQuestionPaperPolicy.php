<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ExamQuestionPaper;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExamQuestionPaperPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ExamQuestionPaper');
    }

    public function view(AuthUser $authUser, ExamQuestionPaper $examQuestionPaper): bool
    {
        return $authUser->can('View:ExamQuestionPaper');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ExamQuestionPaper');
    }

    public function update(AuthUser $authUser, ExamQuestionPaper $examQuestionPaper): bool
    {
        return $authUser->can('Update:ExamQuestionPaper');
    }

    public function delete(AuthUser $authUser, ExamQuestionPaper $examQuestionPaper): bool
    {
        return $authUser->can('Delete:ExamQuestionPaper');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ExamQuestionPaper');
    }

    public function restore(AuthUser $authUser, ExamQuestionPaper $examQuestionPaper): bool
    {
        return $authUser->can('Restore:ExamQuestionPaper');
    }

    public function forceDelete(AuthUser $authUser, ExamQuestionPaper $examQuestionPaper): bool
    {
        return $authUser->can('ForceDelete:ExamQuestionPaper');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ExamQuestionPaper');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ExamQuestionPaper');
    }

    public function replicate(AuthUser $authUser, ExamQuestionPaper $examQuestionPaper): bool
    {
        return $authUser->can('Replicate:ExamQuestionPaper');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ExamQuestionPaper');
    }

    public function import(AuthUser $authUser): bool
    {
        return $authUser->can('Import:ExamQuestionPaper');
    }

    public function export(AuthUser $authUser): bool
    {
        return $authUser->can('Export:ExamQuestionPaper');
    }

}