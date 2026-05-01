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
        $roles = ['administrador', 'agente', 'jefatura', 'secretaria_general', 'presidencia'];
        foreach ($roles as $role) {
            Role::findOrCreate($role, 'web');
        }

        $areaMesaEntrada = Area::firstOrCreate(['nombre' => 'Mesa de Entradas']);
        $areaDireccion = Area::firstOrCreate(['nombre' => 'Dirección']);

        $usuarios = [
            [
                'name' => 'Usuario Admin',
                'email' => 'admin@test.com',
                'password' => Hash::make('password'),
                'role' => 'administrador',
                'agente_data' => [
                    'nombre' => 'Carlos',
                    'apellido' => 'Admin',
                    'dni' => '99999999',
                    'cargo' => 'Administrador del Sistema',
                    'tipo_contrato' => 'Planta Permanente',
                    'area_id' => $areaDireccion->id,
                    'fecha_ingreso' => '2015-01-01',
                ],
            ],
            [
                'name' => 'Usuario Agente',
                'email' => 'agente@test.com',
                'password' => Hash::make('password'),
                'role' => 'agente',
                'agente_data' => [
                    'nombre' => 'Juan',
                    'apellido' => 'Pérez (Agente)',
                    'dni' => '11111111',
                    'cargo' => 'Administrativo',
                    'tipo_contrato' => 'Planta Permanente',
                    'area_id' => $areaMesaEntrada->id,
                    'fecha_ingreso' => '2020-01-01',
                ],
            ],
            [
                'name' => 'Usuario Jefe',
                'email' => 'jefe@test.com',
                'password' => Hash::make('password'),
                'role' => 'jefatura',
                'agente_data' => [
                    'nombre' => 'María',
                    'apellido' => 'Jefe de Área',
                    'dni' => '22222222',
                    'cargo' => 'Jefe de Departamento',
                    'tipo_contrato' => 'Planta Permanente',
                    'area_id' => $areaMesaEntrada->id,
                    'fecha_ingreso' => '2018-06-15',
                ],
            ],
            [
                'name' => 'Usuario Secretario',
                'email' => 'secretario@test.com',
                'password' => Hash::make('password'),
                'role' => 'secretaria_general',
                'agente_data' => [
                    'nombre' => 'Pedro',
                    'apellido' => 'Secretario General',
                    'dni' => '33333333',
                    'cargo' => 'Secretario General',
                    'tipo_contrato' => 'Planta Permanente',
                    'area_id' => $areaDireccion->id,
                    'fecha_ingreso' => '2016-03-01',
                ],
            ],
            [
                'name' => 'Usuario Presidente',
                'email' => 'presidente@test.com',
                'password' => Hash::make('password'),
                'role' => 'presidencia',
                'agente_data' => [
                    'nombre' => 'Ana',
                    'apellido' => 'Presidenta',
                    'dni' => '44444444',
                    'cargo' => 'Presidenta del Tribunal',
                    'tipo_contrato' => 'Planta Permanente',
                    'area_id' => $areaDireccion->id,
                    'fecha_ingreso' => '2010-01-01',
                ],
            ],
        ];

        foreach ($usuarios as $userData) {
            $roleName = $userData['role'];
            $agenteData = $userData['agente_data'];
            
            unset($userData['role'], $userData['agente_data']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            if (!$user->hasRole($roleName)) {
                $user->assignRole($roleName);
            }

            if (!$user->agente) {
                $agenteData['user_id'] = $user->id;
                Agente::create($agenteData);
            } else {
                $user->agente->update($agenteData);
            }
        }
    }
}
