<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Tarif;
use Illuminate\Auth\Access\HandlesAuthorization;

class TarifPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Tarif');
    }

    public function view(AuthUser $authUser, Tarif $tarif): bool
    {
        return $authUser->can('View:Tarif');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Tarif');
    }

    public function update(AuthUser $authUser, Tarif $tarif): bool
    {
        return $authUser->can('Update:Tarif');
    }

    public function delete(AuthUser $authUser, Tarif $tarif): bool
    {
        return $authUser->can('Delete:Tarif');
    }

    public function restore(AuthUser $authUser, Tarif $tarif): bool
    {
        return $authUser->can('Restore:Tarif');
    }

    public function forceDelete(AuthUser $authUser, Tarif $tarif): bool
    {
        return $authUser->can('ForceDelete:Tarif');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Tarif');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Tarif');
    }

    public function replicate(AuthUser $authUser, Tarif $tarif): bool
    {
        return $authUser->can('Replicate:Tarif');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Tarif');
    }

}