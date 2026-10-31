<?php

namespace App\Policies;

use App\Models\Area;
use App\Models\User;

class AreaPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('administrador')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['secretaria_general', 'presidencia']);
    }

    public function view(User $user, Area $area): bool
    {
        return $user->hasAnyRole(['secretaria_general', 'presidencia']);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Area $area): bool
    {
        return false;
    }

    public function delete(User $user, Area $area): bool
    {
        return false;
    }

    public function restore(User $user, Area $area): bool
    {
        return false;
    }

    public function forceDelete(User $user, Area $area): bool
    {
        return false;
    }
}
