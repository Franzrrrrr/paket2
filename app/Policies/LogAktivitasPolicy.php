<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LogAktivitas;
use Illuminate\Auth\Access\HandlesAuthorization;

class LogAktivitasPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LogAktivitas');
    }

    public function view(AuthUser $authUser, LogAktivitas $logAktivitas): bool
    {
        return $authUser->can('View:LogAktivitas');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LogAktivitas');
    }

    public function update(AuthUser $authUser, LogAktivitas $logAktivitas): bool
    {
        return $authUser->can('Update:LogAktivitas');
    }

    public function delete(AuthUser $authUser, LogAktivitas $logAktivitas): bool
    {
        return $authUser->can('Delete:LogAktivitas');
    }

    public function restore(AuthUser $authUser, LogAktivitas $logAktivitas): bool
    {
        return $authUser->can('Restore:LogAktivitas');
    }

    public function forceDelete(AuthUser $authUser, LogAktivitas $logAktivitas): bool
    {
        return $authUser->can('ForceDelete:LogAktivitas');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LogAktivitas');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LogAktivitas');
    }

    public function replicate(AuthUser $authUser, LogAktivitas $logAktivitas): bool
    {
        return $authUser->can('Replicate:LogAktivitas');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LogAktivitas');
    }

}