<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ExamResult;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExamResultPolicy
{
    use HandlesAuthorization;

    private function isBranchAdmin(AuthUser $authUser): bool
    {
        return $authUser->hasAnyRole(['super_admin', 'admin', 'principal']);
    }

    private function isSameBranch(AuthUser $authUser, ExamResult $examResult): bool
    {
        $branchId = method_exists($authUser, 'getAttribute')
            ? $authUser->getAttribute('branch_id')
            : ($authUser->branch_id ?? null);

        $examBranchId = method_exists($examResult, 'getRelation')
            ? (($examResult->relationLoaded('exam') ? $examResult->getRelation('exam') : null)?->branch_id ?? null)
            : ($examResult->exam?->branch_id ?? null);

        return $this->isBranchAdmin($authUser)
            && is_numeric($branchId)
            && is_numeric($examBranchId)
            && (int) $branchId === (int) $examBranchId;
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ExamResult') || $this->isBranchAdmin($authUser);
    }

    public function view(AuthUser $authUser, ExamResult $examResult): bool
    {
        return $authUser->can('View:ExamResult') || $this->isSameBranch($authUser, $examResult);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ExamResult');
    }

    public function update(AuthUser $authUser, ExamResult $examResult): bool
    {
        return $examResult->canBeEditedBy($authUser);
    }

    public function delete(AuthUser $authUser, ExamResult $examResult): bool
    {
        return $examResult->canBeEditedBy($authUser) && $authUser->hasRole('super_admin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ExamResult');
    }

    public function restore(AuthUser $authUser, ExamResult $examResult): bool
    {
        return $authUser->can('Restore:ExamResult');
    }

    public function forceDelete(AuthUser $authUser, ExamResult $examResult): bool
    {
        return $authUser->can('ForceDelete:ExamResult');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ExamResult');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ExamResult');
    }

    public function replicate(AuthUser $authUser, ExamResult $examResult): bool
    {
        return $authUser->can('Replicate:ExamResult');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ExamResult');
    }

    public function import(AuthUser $authUser): bool
    {
        return $authUser->can('Import:ExamResult');
    }

    public function export(AuthUser $authUser): bool
    {
        return $authUser->can('Export:ExamResult');
    }

}