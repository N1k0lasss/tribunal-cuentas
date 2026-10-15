<?php

namespace App\Policies;

use App\Models\Feriados;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FeriadosPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('administrador')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Feriados $feriado): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Feriados $feriado): bool
    {
        return false;
    }

    public function delete(User $user, Feriados $feriado): bool
    {
        return false;
    }

    public function restore(User $user, Feriados $feriado): bool
    {
        return false;
    }

    public function forceDelete(User $user, Feriados $feriado): bool
    {
        return false;
    }
}
