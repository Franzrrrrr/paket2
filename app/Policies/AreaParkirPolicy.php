<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AreaParkir;
use Illuminate\Auth\Access\HandlesAuthorization;

class AreaParkirPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AreaParkir');
    }

    public function view(AuthUser $authUser, AreaParkir $areaParkir): bool
    {
        return $authUser->can('View:AreaParkir');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AreaParkir');
    }

    public function update(AuthUser $authUser, AreaParkir $areaParkir): bool
    {
        return $authUser->can('Update:AreaParkir');
    }

    public function delete(AuthUser $authUser, AreaParkir $areaParkir): bool
    {
        return $authUser->can('Delete:AreaParkir');
    }

    public function restore(AuthUser $authUser, AreaParkir $areaParkir): bool
    {
        return $authUser->can('Restore:AreaParkir');
    }

    public function forceDelete(AuthUser $authUser, AreaParkir $areaParkir): bool
    {
        return $authUser->can('ForceDelete:AreaParkir');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AreaParkir');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AreaParkir');
    }

    public function replicate(AuthUser $authUser, AreaParkir $areaParkir): bool
    {
        return $authUser->can('Replicate:AreaParkir');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AreaParkir');
    }

}