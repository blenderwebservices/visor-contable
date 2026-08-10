<?php

namespace App\Policies;

use App\Models\Folder;
use App\Models\User;

class FolderPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Folder $folder): bool
    {
        if ($user->isSupervisor()) {
            return $user->supervisedGroups()->where('groups.id', $folder->group_id)->exists();
        }

        if ($user->isReader()) {
            if ($folder->group_id !== $user->group_id) {
                return false;
            }

            if ($user->has_restricted_folders) {
                return $user->allowedFolders()->where('folders.id', $folder->id)->exists();
            }

            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isSupervisor() || $user->isAdmin();
    }

    public function update(User $user, Folder $folder): bool
    {
        if ($user->isSupervisor()) {
            return $user->supervisedGroups()->where('groups.id', $folder->group_id)->exists();
        }

        return false;
    }

    public function delete(User $user, Folder $folder): bool
    {
        if ($user->isSupervisor()) {
            return $user->supervisedGroups()->where('groups.id', $folder->group_id)->exists();
        }

        return false;
    }

    public function restore(User $user, Folder $folder): bool
    {
        if ($user->isSupervisor()) {
            return $user->supervisedGroups()->where('groups.id', $folder->group_id)->exists();
        }

        return false;
    }

    public function forceDelete(User $user, Folder $folder): bool
    {
        return false;
    }
}
