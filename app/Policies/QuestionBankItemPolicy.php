<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\QuestionBankItem;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuestionBankItemPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:QuestionBankItem');
    }

    public function view(AuthUser $authUser, QuestionBankItem $questionBankItem): bool
    {
        return $authUser->can('View:QuestionBankItem');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:QuestionBankItem');
    }

    public function update(AuthUser $authUser, QuestionBankItem $questionBankItem): bool
    {
        return $authUser->can('Update:QuestionBankItem');
    }

    public function delete(AuthUser $authUser, QuestionBankItem $questionBankItem): bool
    {
        return $authUser->can('Delete:QuestionBankItem');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:QuestionBankItem');
    }

    public function restore(AuthUser $authUser, QuestionBankItem $questionBankItem): bool
    {
        return $authUser->can('Restore:QuestionBankItem');
    }

    public function forceDelete(AuthUser $authUser, QuestionBankItem $questionBankItem): bool
    {
        return $authUser->can('ForceDelete:QuestionBankItem');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:QuestionBankItem');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:QuestionBankItem');
    }

    public function replicate(AuthUser $authUser, QuestionBankItem $questionBankItem): bool
    {
        return $authUser->can('Replicate:QuestionBankItem');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:QuestionBankItem');
    }

    public function import(AuthUser $authUser): bool
    {
        return $authUser->can('Import:QuestionBankItem');
    }

    public function export(AuthUser $authUser): bool
    {
        return $authUser->can('Export:QuestionBankItem');
    }

}