<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SectionSubject;
use Illuminate\Auth\Access\HandlesAuthorization;

class SectionSubjectPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SectionSubject');
    }

    public function view(AuthUser $authUser, SectionSubject $sectionSubject): bool
    {
        return $authUser->can('View:SectionSubject');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SectionSubject');
    }

    public function update(AuthUser $authUser, SectionSubject $sectionSubject): bool
    {
        return $authUser->can('Update:SectionSubject');
    }

    public function delete(AuthUser $authUser, SectionSubject $sectionSubject): bool
    {
        return $authUser->can('Delete:SectionSubject');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SectionSubject');
    }

    public function restore(AuthUser $authUser, SectionSubject $sectionSubject): bool
    {
        return $authUser->can('Restore:SectionSubject');
    }

    public function forceDelete(AuthUser $authUser, SectionSubject $sectionSubject): bool
    {
        return $authUser->can('ForceDelete:SectionSubject');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SectionSubject');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SectionSubject');
    }

    public function replicate(AuthUser $authUser, SectionSubject $sectionSubject): bool
    {
        return $authUser->can('Replicate:SectionSubject');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SectionSubject');
    }

}