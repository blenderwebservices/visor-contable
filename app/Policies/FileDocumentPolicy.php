<?php

namespace App\Policies;

use App\Models\FileDocument;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FileDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FileDocument $fileDocument): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }

    public function update(User $user, FileDocument $fileDocument): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }

    public function delete(User $user, FileDocument $fileDocument): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }

    public function restore(User $user, FileDocument $fileDocument): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }

    public function forceDelete(User $user, FileDocument $fileDocument): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }
}
