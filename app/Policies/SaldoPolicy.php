<?php

namespace App\Policies;

use App\Models\Saldo;
use App\Models\User;

class SaldoPolicy
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

    public function view(User $user, Saldo $saldo): bool
    {
        if ($user->hasAnyRole(['secretaria_general', 'presidencia'])) {
            return true;
        }

        return $user->agente?->id === $saldo->agente_id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Saldo $saldo): bool
    {
        return false;
    }

    public function delete(User $user, Saldo $saldo): bool
    {
        return false;
    }

    public function restore(User $user, Saldo $saldo): bool
    {
        return false;
    }

    public function forceDelete(User $user, Saldo $saldo): bool
    {
        return false;
    }
}
