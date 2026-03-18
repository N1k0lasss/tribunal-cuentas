<?php

namespace Database\Seeders;

use App\Models\Agente;
use App\Models\Area;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TestWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Asegurar roles
        $roles = ['administrador', 'agente', 'jefatura', 'secretaria_general', 'presidencia'];
        foreach ($roles as $role) {
            Role::findOrCreate($role, 'web');
        }

        // 2. Crear usuarios de prueba para cada rol
        $usuarios = [
            [
                'name' => 'Usuario Admin',
                'email' => 'admin@test.com',
                'password' => Hash::make('password'),
                'role' => 'administrador',
            ],
            [
                'name' => 'Usuario Agente',
                'email' => 'agente@test.com',
                'password' => Hash::make('password'),
                'role' => 'agente',
                // Datos para la ficha de agente
                'agente_data' => [
                    'nombre' => 'Juan',
                    'apellido' => 'Pérez (Agente)',
                    'dni' => '11111111',
                    'cargo' => 'Administrativo',
                    'tipo_contrato' => 'Planta Permanente',
                    'fecha_ingreso' => '2020-01-01',
                ]
            ],
            [
                'name' => 'Usuario Jefe',
                'email' => 'jefe@test.com',
                'password' => Hash::make('password'),
                'role' => 'jefatura',
            ],
            [
                'name' => 'Usuario Secretario',
                'email' => 'secretario@test.com',
                'password' => Hash::make('password'),
                'role' => 'secretaria_general',
            ],
            [
                'name' => 'Usuario Presidente',
                'email' => 'presidente@test.com',
                'password' => Hash::make('password'),
                'role' => 'presidencia',
            ],
        ];

        // Crear una categoría de Área Genérica para asignar al agente
        $area = Area::firstOrCreate(
            ['nombre' => 'Mesa de Entradas']
        );

        foreach ($usuarios as $userData) {
            $roleName = $userData['role'];
            $agenteData = $userData['agente_data'] ?? null;
            
            unset($userData['role'], $userData['agente_data']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // Asignar el rol
            if (!$user->hasRole($roleName)) {
                $user->assignRole($roleName);
            }

            // Si es agente, crear la ficha vinculada
            if ($agenteData && !$user->agente) {
                $agenteData['user_id'] = $user->id;
                $agenteData['area_id'] = $area->id;
                
                app(\App\Models\Agente::class)->create($agenteData);
            }
        }
    }
}
