<?php

namespace App\Policies;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FolderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Folder $folder): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }

    public function update(User $user, Folder $folder): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }

    public function delete(User $user, Folder $folder): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }

    public function restore(User $user, Folder $folder): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }

    public function forceDelete(User $user, Folder $folder): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }
}
