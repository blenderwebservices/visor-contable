<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GroupPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }

    public function view(User $user, Group $group): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }

    public function update(User $user, Group $group): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }

    public function delete(User $user, Group $group): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }

    public function restore(User $user, Group $group): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }

    public function forceDelete(User $user, Group $group): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }
}
