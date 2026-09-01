<?php

namespace Database\Seeders;

use App\Models\Agente;
use App\Models\Area;
use App\Models\Solicitud;
use App\Models\Aprobacion;
use App\Models\Documento;
use App\Models\TipoSolicitud;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class TestWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['administrador', 'agente', 'jefatura', 'secretaria_general', 'presidencia'];
        foreach ($roles as $role) {
            Role::findOrCreate($role, 'web');
        }

        $areaMesaEntrada = Area::firstOrCreate(['nombre' => 'Mesa de Entradas']);
        $areaDireccion = Area::firstOrCreate(['nombre' => 'Dirección']);
        $areaAuditoria = Area::firstOrCreate(['nombre' => 'Auditoría']);

        $usuarios = [
            [
                'name' => 'Usuario Admin',
                'email' => 'admin@test.com',
                'password' => 'password',
                'role' => 'administrador',
                'agente_data' => [
                    'nombre' => 'Carlos',
                    'apellido' => 'Admin',
                    'dni' => '99999999',
                    'cargo' => 'Administrador del Sistema',
                    'situacion_revista' => 'Planta Permanente',
                    'area_id' => $areaDireccion->id,
                    'fecha_ingreso' => '2015-01-01',
                ],
            ],
            [
                'name' => 'Usuario Jefe',
                'email' => 'jefe@test.com',
                'password' => 'password',
                'role' => 'jefatura',
                'agente_data' => [
                    'nombre' => 'María',
                    'apellido' => 'Gómez (Jefa)',
                    'dni' => '22222222',
                    'cargo' => 'Jefe de Departamento',
                    'situacion_revista' => 'Planta Permanente',
                    'area_id' => $areaMesaEntrada->id,
                    'fecha_ingreso' => '2018-06-15',
                ],
            ],
            [
                'name' => 'Usuario Secretario',
                'email' => 'secretario@test.com',
                'password' => 'password',
                'role' => 'secretaria_general',
                'agente_data' => [
                    'nombre' => 'Pedro',
                    'apellido' => 'Martínez (Secretario)',
                    'dni' => '33333333',
                    'cargo' => 'Secretario General',
                    'situacion_revista' => 'Planta Permanente',
                    'area_id' => $areaDireccion->id,
                    'fecha_ingreso' => '2016-03-01',
                ],
            ],
            [
                'name' => 'Usuario Presidente',
                'email' => 'presidente@test.com',
                'password' => 'password',
                'role' => 'presidencia',
                'agente_data' => [
                    'nombre' => 'Ana',
                    'apellido' => 'López (Presidenta)',
                    'dni' => '44444444',
                    'cargo' => 'Presidenta del Tribunal',
                    'situacion_revista' => 'Planta Permanente',
                    'area_id' => $areaDireccion->id,
                    'fecha_ingreso' => '2010-01-01',
                ],
            ],
            [
                'name' => 'Juan Pérez',
                'email' => 'agente1@test.com',
                'password' => 'password',
                'role' => 'agente',
                'agente_data' => [
                    'nombre' => 'Juan',
                    'apellido' => 'Pérez',
                    'dni' => '11111111',
                    'cargo' => 'Administrativo',
                    'situacion_revista' => 'Planta Permanente',
                    'area_id' => $areaMesaEntrada->id,
                    'fecha_ingreso' => '2020-01-01',
                ],
            ],
            [
                'name' => 'Lucía Fernández',
                'email' => 'agente2@test.com',
                'password' => 'password',
                'role' => 'agente',
                'agente_data' => [
                    'nombre' => 'Lucía',
                    'apellido' => 'Fernández',
                    'dni' => '11111112',
                    'cargo' => 'Auditor Junior',
                    'situacion_revista' => 'Contratado',
                    'area_id' => $areaAuditoria->id,
                    'fecha_ingreso' => '2022-05-10',
                ],
            ],
        ];

        $users = [];
        foreach ($usuarios as $userData) {
            $roleName = $userData['role'];
            $agenteData = $userData['agente_data'];
            unset($userData['role'], $userData['agente_data']);

            $user = User::firstOrCreate(['email' => $userData['email']], $userData);
            if (!$user->hasRole($roleName)) {
                $user->assignRole($roleName);
            }

            if (!$user->agente) {
                $agenteData['user_id'] = $user->id;
                $agente = Agente::create($agenteData);
            } else {
                $user->agente->update($agenteData);
                $agente = $user->agente;
            }

            $users[$roleName === 'agente' ? $agenteData['nombre'] : $roleName] = $user;
        }

        // Crear solicitudes de ejemplo
        $tipoLicenciaOrdinaria = TipoSolicitud::where('nombre', 'Licencia Anual Ordinaria')->first();
        $tipoEnfermedad = TipoSolicitud::where('nombre', 'Enfermedad Común')->first();

        if (!$tipoLicenciaOrdinaria || !$tipoEnfermedad) {
            return;
        }

        $agente1 = $users['Juan']->agente;
        $agente2 = $users['Lucía']->agente;

        // 1. Agente Juan Pérez EN LICENCIA APROBADA ACTUALMENTE (para que aparezca en el calendario)
        $sol1 = Solicitud::create([
            'agente_id' => $agente1->id,
            'tipo_solicitud_id' => $tipoLicenciaOrdinaria->id,
            'fecha_solicitud' => Carbon::now()->subDays(10),
            'fecha_inicio' => Carbon::now()->subDays(2),
            'fecha_fin' => Carbon::now()->addDays(5),
            'dias_solicitados' => 8,
            'estado' => Solicitud::ESTADO_APROBADO,
        ]);

        $sol1->aprobaciones()->createMany([
            ['etapa' => Solicitud::ETAPA_JEFATURA, 'usuario_id' => $users['jefatura']->id, 'estado' => 'aprobado', 'fecha' => Carbon::now()->subDays(9)],
            ['etapa' => Solicitud::ETAPA_SECRETARIA, 'usuario_id' => $users['secretaria_general']->id, 'estado' => 'aprobado', 'fecha' => Carbon::now()->subDays(8)],
            ['etapa' => Solicitud::ETAPA_DIRECCION, 'usuario_id' => $users['presidencia']->id, 'estado' => 'aprobado', 'fecha' => Carbon::now()->subDays(7)],
        ]);

        // 2. Agente Lucía Fernández EN REVISIÓN por Jefatura (con documento adjunto)
        $sol2 = Solicitud::create([
            'agente_id' => $agente2->id,
            'tipo_solicitud_id' => $tipoEnfermedad->id,
            'fecha_solicitud' => Carbon::now(),
            'fecha_inicio' => Carbon::now()->addDays(1),
            'fecha_fin' => Carbon::now()->addDays(3),
            'dias_solicitados' => 3,
            'estado' => Solicitud::ESTADO_PENDIENTE,
            'motivo' => 'Gripe fuerte',
        ]);
        
        $sol2->aprobaciones()->create([
            'etapa' => Solicitud::ETAPA_JEFATURA,
            'estado' => 'pendiente',
        ]);

        $sol2->documentos()->create([
            'nombre_archivo' => 'Certificado_Medico.pdf',
            'tipo_documento' => 'certificado_medico',
            'ruta' => 'documentos/demo.pdf', // Asume que no validará existencia física
        ]);

        // 3. Agente Juan Pérez EN REVISIÓN por Secretaria (Jefatura ya aprobó)
        $sol3 = Solicitud::create([
            'agente_id' => $agente1->id,
            'tipo_solicitud_id' => $tipoLicenciaOrdinaria->id,
            'fecha_solicitud' => Carbon::now()->subDays(2),
            'fecha_inicio' => Carbon::now()->addDays(10),
            'fecha_fin' => Carbon::now()->addDays(15),
            'dias_solicitados' => 6,
            'estado' => Solicitud::ESTADO_EN_REVISION,
            'motivo' => 'Vacaciones pendientes de 2024',
        ]);

        $sol3->aprobaciones()->createMany([
            ['etapa' => Solicitud::ETAPA_JEFATURA, 'usuario_id' => $users['jefatura']->id, 'estado' => 'aprobado', 'fecha' => Carbon::now()->subDays(1)],
            ['etapa' => Solicitud::ETAPA_SECRETARIA, 'estado' => 'pendiente'],
        ]);
        
        // 4. Agente Lucía Fernández EN REVISIÓN por Presidencia (Secretaría ya aprobó)
        $sol4 = Solicitud::create([
            'agente_id' => $agente2->id,
            'tipo_solicitud_id' => $tipoLicenciaOrdinaria->id,
            'fecha_solicitud' => Carbon::now()->subDays(5),
            'fecha_inicio' => Carbon::now()->addDays(20),
            'fecha_fin' => Carbon::now()->addDays(25),
            'dias_solicitados' => 6,
            'estado' => Solicitud::ESTADO_EN_REVISION,
        ]);

        $sol4->aprobaciones()->createMany([
            ['etapa' => Solicitud::ETAPA_JEFATURA, 'usuario_id' => $users['jefatura']->id, 'estado' => 'aprobado', 'fecha' => Carbon::now()->subDays(4)],
            ['etapa' => Solicitud::ETAPA_SECRETARIA, 'usuario_id' => $users['secretaria_general']->id, 'estado' => 'aprobado', 'fecha' => Carbon::now()->subDays(3)],
            ['etapa' => Solicitud::ETAPA_DIRECCION, 'estado' => 'pendiente'],
        ]);
        // Nuevas solicitudes pendientes / en revisión para probar
        // Agente Juan Pérez (Pendiente Jefatura)
        $sol5 = Solicitud::create([
            'agente_id' => $agente1->id,
            'tipo_solicitud_id' => $tipoEnfermedad->id,
            'fecha_solicitud' => Carbon::now(),
            'fecha_inicio' => Carbon::now()->addDays(30),
            'fecha_fin' => Carbon::now()->addDays(32),
            'dias_solicitados' => 3,
            'estado' => Solicitud::ESTADO_PENDIENTE,
            'motivo' => 'Turno médico especialista',
        ]);
        $sol5->aprobaciones()->create([
            'etapa' => Solicitud::ETAPA_JEFATURA,
            'estado' => 'pendiente',
        ]);

        // Agente Lucía Fernández (Pendiente Jefatura)
        $sol6 = Solicitud::create([
            'agente_id' => $agente2->id,
            'tipo_solicitud_id' => $tipoLicenciaOrdinaria->id,
            'fecha_solicitud' => Carbon::now(),
            'fecha_inicio' => Carbon::now()->addDays(40),
            'fecha_fin' => Carbon::now()->addDays(45),
            'dias_solicitados' => 6,
            'estado' => Solicitud::ESTADO_PENDIENTE,
            'motivo' => 'Días de descanso pendientes',
        ]);
        $sol6->aprobaciones()->create([
            'etapa' => Solicitud::ETAPA_JEFATURA,
            'estado' => 'pendiente',
        ]);

        // Agente Juan Pérez (En revisión Secretaría)
        $sol7 = Solicitud::create([
            'agente_id' => $agente1->id,
            'tipo_solicitud_id' => $tipoEnfermedad->id,
            'fecha_solicitud' => Carbon::now()->subDays(1),
            'fecha_inicio' => Carbon::now()->addDays(50),
            'fecha_fin' => Carbon::now()->addDays(51),
            'dias_solicitados' => 2,
            'estado' => Solicitud::ESTADO_EN_REVISION,
            'motivo' => 'Estudios médicos de rutina',
        ]);
        $sol7->aprobaciones()->createMany([
            ['etapa' => Solicitud::ETAPA_JEFATURA, 'usuario_id' => $users['jefatura']->id, 'estado' => 'aprobado', 'fecha' => Carbon::now()],
            ['etapa' => Solicitud::ETAPA_SECRETARIA, 'estado' => 'pendiente'],
        ]);

        // Agente Lucía Fernández (En revisión Presidencia)
        $sol8 = Solicitud::create([
            'agente_id' => $agente2->id,
            'tipo_solicitud_id' => $tipoLicenciaOrdinaria->id,
            'fecha_solicitud' => Carbon::now()->subDays(3),
            'fecha_inicio' => Carbon::now()->addDays(60),
            'fecha_fin' => Carbon::now()->addDays(70),
            'dias_solicitados' => 11,
            'estado' => Solicitud::ESTADO_EN_REVISION,
            'motivo' => 'Licencia ordinaria planificada',
        ]);
        $sol8->aprobaciones()->createMany([
            ['etapa' => Solicitud::ETAPA_JEFATURA, 'usuario_id' => $users['jefatura']->id, 'estado' => 'aprobado', 'fecha' => Carbon::now()->subDays(2)],
            ['etapa' => Solicitud::ETAPA_SECRETARIA, 'usuario_id' => $users['secretaria_general']->id, 'estado' => 'aprobado', 'fecha' => Carbon::now()->subDays(1)],
            ['etapa' => Solicitud::ETAPA_DIRECCION, 'estado' => 'pendiente'],
        ]);
    }
}
