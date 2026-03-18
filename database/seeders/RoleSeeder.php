<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Los roles del sistema para el workflow de solicitudes.
     *
     * @var list<string>
     */
    protected array $roles = [
        'administrador',
        'agente',
        'jefatura',
        'secretaria_general',
        'presidencia',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->roles as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
