<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ExamMark;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExamMarkPolicy
{
    use HandlesAuthorization;

    private function isBranchAdmin(AuthUser $authUser): bool
    {
        return $authUser->hasAnyRole(['super_admin', 'admin', 'principal', 'teacher']);
    }

    private function isSameBranch(AuthUser $authUser, ExamMark $examMark): bool
    {
        $branchId = method_exists($authUser, 'getAttribute')
            ? $authUser->getAttribute('branch_id')
            : ($authUser->branch_id ?? null);

        $examBranchId = method_exists($examMark, 'getRelation')
            ? ($examMark->relationLoaded('exam') ? $examMark->getRelation('exam') : null)?->branch_id
                ?? ($examMark->relationLoaded('entry') ? $examMark->getRelation('entry') : null)?->exam?->branch_id
                ?? null
            : ($examMark->exam?->branch_id ?? $examMark->entry?->exam?->branch_id ?? null);

        return $this->isBranchAdmin($authUser)
            && is_numeric($branchId)
            && is_numeric($examBranchId)
            && (int) $branchId === (int) $examBranchId;
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ExamMark') || $this->isBranchAdmin($authUser);
    }

    public function view(AuthUser $authUser, ExamMark $examMark): bool
    {
        return $authUser->can('View:ExamMark') || $this->isSameBranch($authUser, $examMark);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ExamMark');
    }

    public function update(AuthUser $authUser, ExamMark $examMark): bool
    {
        return $examMark->canBeEditedBy($authUser);
    }

    public function delete(AuthUser $authUser, ExamMark $examMark): bool
    {
        return $examMark->canBeEditedBy($authUser) && $authUser->hasRole('super_admin');
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