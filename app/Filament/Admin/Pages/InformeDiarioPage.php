<?php

namespace App\Filament\Admin\Pages;

use App\Models\Agente;
use App\Models\InformeDiario;
use App\Models\InformeDiarioDetalle;
use App\Models\Solicitud;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class InformeDiarioPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected string $view = 'filament.admin.pages.informe-diario';

    protected static ?string $title = 'Informe Diario de Novedades';

    protected static ?string $navigationLabel = 'Informe Diario';

    protected static string|\UnitEnum|null $navigationGroup = 'Flujo de licencias';

    protected static ?string $slug = 'informe-diario';

    protected static ?int $navigationSort = 8;

    public string $fecha = '';

    public string $observacionesGenerales = '';

    public string $destinatario = 'Secretaría General';

    public array $detalles = [];

    public ?string $filtroArea = null;

    public ?string $filtroNovedad = null;

    public ?string $ultimaSincronizacion = null;

    public bool $mostrarModalExportar = false;

    public array $resumenExportacion = [];

    public function mount(): void
    {
        $this->fecha = date('Y-m-d');
        $this->cargarInforme();
    }

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user?->hasRole(['administrador', 'secretaria_general']) ?? false;
    }

    public function updatedFecha(): void
    {
        $this->cargarInforme();
    }

    public function esFechaFutura(): bool
    {
        return Carbon::parse($this->fecha)->isAfter(Carbon::today());
    }

    public function cargarInforme(): void
    {
        if (empty($this->fecha)) {
            $this->fecha = date('Y-m-d');
        }

        $informe = InformeDiario::with(['detalles.agente.area', 'detalles.tipoSolicitud'])
            ->whereDate('fecha', $this->fecha)
            ->first();

        if ($informe) {
            $this->observacionesGenerales = $informe->observaciones_generales ?? '';
            $this->destinatario = $informe->destinatario ?? 'Secretaría General';
            $this->ultimaSincronizacion = $informe->updated_at?->format('d/m/Y H:i');

            $existingDetalles = $informe->detalles->keyBy('agente_id');
            $allAgentes = Agente::with('area')->orderBy('apellido')->orderBy('nombre')->get();

            $items = [];
            foreach ($allAgentes as $agente) {
                if ($existingDetalles->has($agente->id)) {
                    $det = $existingDetalles->get($agente->id);
                    $items[] = [
                        'agente_id' => $agente->id,
                        'agente_nombre' => $agente->apellido.', '.$agente->nombre,
                        'agente_dni' => $agente->dni,
                        'area_id' => $agente->area_id,
                        'area_nombre' => $agente->area->nombre ?? 'Sin Área',
                        'tipo_novedad' => $det->tipo_novedad ?? 'presente',
                        'solicitud_id' => $det->solicitud_id,
                        'tipo_solicitud_id' => $det->tipo_solicitud_id,
                        'tipo_licencia_nombre' => $det->tipoSolicitud->nombre ?? null,
                        'hora_llegada' => $det->hora_llegada ?? '',
                        'minutos_tardanza' => $det->minutos_tardanza ?? null,
                        'observaciones' => $det->observaciones ?? '',
                    ];
                } else {
                    $items[] = $this->buildNewAgenteDetalle($agente);
                }
            }
            $this->detalles = $items;
        } else {
            $this->observacionesGenerales = '';
            $this->destinatario = 'Secretaría General';
            $this->ultimaSincronizacion = null;
            $this->sincronizarAgentesYLicencias();
        }
    }

    public function sincronizarAgentesYLicencias(): void
    {
        if ($this->esFechaFutura()) {
            Notification::make()
                ->title('Acceso denegado')
                ->body('No es posible sincronizar licencias para fechas futuras.')
                ->danger()
                ->send();

            return;
        }

        $allAgentes = Agente::with('area')->orderBy('apellido')->orderBy('nombre')->get();
        $fechaCarbon = Carbon::parse($this->fecha);

        // Obtener solicitudes aprobadas para la fecha seleccionada
        $solicitudes = Solicitud::with('tipoSolicitud')
            ->where('estado', 'aprobado')
            ->whereDate('fecha_inicio', '<=', $fechaCarbon)
            ->whereDate('fecha_fin', '>=', $fechaCarbon)
            ->get()
            ->keyBy('agente_id');

        $items = [];
        foreach ($allAgentes as $agente) {
            if ($solicitudes->has($agente->id)) {
                $solicitud = $solicitudes->get($agente->id);
                $items[] = [
                    'agente_id' => $agente->id,
                    'agente_nombre' => $agente->apellido.', '.$agente->nombre,
                    'agente_dni' => $agente->dni,
                    'area_id' => $agente->area_id,
                    'area_nombre' => $agente->area->nombre ?? 'Sin Área',
                    'tipo_novedad' => 'licencia',
                    'solicitud_id' => $solicitud->id,
                    'tipo_solicitud_id' => $solicitud->tipo_solicitud_id,
                    'tipo_licencia_nombre' => $solicitud->tipoSolicitud->nombre ?? 'Licencia',
                    'hora_llegada' => '',
                    'minutos_tardanza' => null,
                    'observaciones' => 'Licencia aprobada ('.($solicitud->tipoSolicitud->nombre ?? '').')',
                ];
            } else {
                $items[] = [
                    'agente_id' => $agente->id,
                    'agente_nombre' => $agente->apellido.', '.$agente->nombre,
                    'agente_dni' => $agente->dni,
                    'area_id' => $agente->area_id,
                    'area_nombre' => $agente->area->nombre ?? 'Sin Área',
                    'tipo_novedad' => 'presente',
                    'solicitud_id' => null,
                    'tipo_solicitud_id' => null,
                    'tipo_licencia_nombre' => null,
                    'hora_llegada' => '',
                    'minutos_tardanza' => null,
                    'observaciones' => '',
                ];
            }
        }
        $this->detalles = $items;
    }

    private function buildNewAgenteDetalle(Agente $agente): array
    {
        $fechaCarbon = Carbon::parse($this->fecha);
        $solicitud = Solicitud::with('tipoSolicitud')
            ->where('agente_id', $agente->id)
            ->where('estado', 'aprobado')
            ->whereDate('fecha_inicio', '<=', $fechaCarbon)
            ->whereDate('fecha_fin', '>=', $fechaCarbon)
            ->first();

        if ($solicitud) {
            return [
                'agente_id' => $agente->id,
                'agente_nombre' => $agente->apellido.', '.$agente->nombre,
                'agente_dni' => $agente->dni,
                'area_id' => $agente->area_id,
                'area_nombre' => $agente->area->nombre ?? 'Sin Área',
                'tipo_novedad' => 'licencia',
                'solicitud_id' => $solicitud->id,
                'tipo_solicitud_id' => $solicitud->tipo_solicitud_id,
                'tipo_licencia_nombre' => $solicitud->tipoSolicitud->nombre ?? 'Licencia',
                'hora_llegada' => '',
                'minutos_tardanza' => null,
                'observaciones' => 'Licencia aprobada ('.($solicitud->tipoSolicitud->nombre ?? '').')',
            ];
        }

        return [
            'agente_id' => $agente->id,
            'agente_nombre' => $agente->apellido.', '.$agente->nombre,
            'agente_dni' => $agente->dni,
            'area_id' => $agente->area_id,
            'area_nombre' => $agente->area->nombre ?? 'Sin Área',
            'tipo_novedad' => 'presente',
            'solicitud_id' => null,
            'tipo_solicitud_id' => null,
            'tipo_licencia_nombre' => null,
            'hora_llegada' => '',
            'minutos_tardanza' => null,
            'observaciones' => '',
        ];
    }

    public function esAdmin(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user?->hasRole('administrador') ?? false;
    }

    public function guardarInforme(): void
    {
        if ($this->esFechaFutura()) {
            Notification::make()
                ->title('Acceso denegado')
                ->body('No es posible guardar o modificar el informe para fechas futuras.')
                ->danger()
                ->send();

            return;
        }

        if (! $this->esAdmin()) {
            Notification::make()
                ->title('Acceso denegado')
                ->body('Solo el usuario Administrador puede modificar o guardar el Informe Diario.')
                ->danger()
                ->send();

            return;
        }

        $informe = InformeDiario::updateOrCreate(
            ['fecha' => $this->fecha],
            [
                'observaciones_generales' => $this->observacionesGenerales,
                'destinatario' => $this->destinatario ?: 'Secretaría General',
                'user_id' => auth()->id(),
            ]
        );

        foreach ($this->detalles as $det) {
            InformeDiarioDetalle::updateOrCreate(
                [
                    'informe_diario_id' => $informe->id,
                    'agente_id' => $det['agente_id'],
                ],
                [
                    'tipo_novedad' => $det['tipo_novedad'],
                    'solicitud_id' => $det['solicitud_id'] ?? null,
                    'tipo_solicitud_id' => $det['tipo_solicitud_id'] ?? null,
                    'hora_llegada' => $det['hora_llegada'] ?? null,
                    'minutos_tardanza' => ! empty($det['minutos_tardanza']) ? (int) $det['minutos_tardanza'] : null,
                    'observaciones' => $det['observaciones'] ?? null,
                ]
            );
        }

        $informeRefrescado = InformeDiario::whereDate('fecha', $this->fecha)->first();
        $this->ultimaSincronizacion = $informeRefrescado?->updated_at?->format('d/m/Y H:i');

        Notification::make()
            ->title('Informe Diario Guardado')
            ->body('Las novedades del '.Carbon::parse($this->fecha)->format('d/m/Y').' se guardaron correctamente.')
            ->success()
            ->send();
    }

    public function prepararExportacion(): void
    {
        $cantidades = [
            'presente' => 0,
            'licencia' => 0,
            'falta_justificada' => 0,
            'ausente_sin_aviso' => 0,
            'tardanza' => 0,
        ];

        foreach ($this->detalles as $det) {
            $tipo = $det['tipo_novedad'] ?? 'presente';
            if (isset($cantidades[$tipo])) {
                $cantidades[$tipo]++;
            }
        }

        $this->resumenExportacion = $cantidades;
        $this->mostrarModalExportar = true;
    }

    public function confirmarExportarPdf()
    {
        $this->mostrarModalExportar = false;

        if ($this->esFechaFutura()) {
            Notification::make()
                ->title('Acceso denegado')
                ->body('No es posible generar o modificar el informe para fechas futuras.')
                ->danger()
                ->send();

            return;
        }

        if ($this->esAdmin()) {
            $this->guardarInforme();
        }

        $cantidades = [
            'presente' => 0,
            'licencia' => 0,
            'falta_justificada' => 0,
            'ausente_sin_aviso' => 0,
            'tardanza' => 0,
        ];

        $labels = [
            'presente' => 'Presente',
            'licencia' => 'Licencia',
            'falta_justificada' => 'Falta Justificada',
            'ausente_sin_aviso' => 'Ausente sin Aviso',
            'tardanza' => 'Tardanza',
        ];

        $detallesProcesados = [];
        foreach ($this->detalles as $det) {
            $tipo = $det['tipo_novedad'] ?? 'presente';
            if (isset($cantidades[$tipo])) {
                $cantidades[$tipo]++;
            }

            $detallesProcesados[] = array_merge($det, [
                'tipo_novedad_label' => $labels[$tipo] ?? $tipo,
            ]);
        }

        $pdf = Pdf::loadView('filament.admin.pages.informe-diario-pdf', [
            'fecha' => $this->fecha,
            'fechaFormatted' => Carbon::parse($this->fecha)->format('d/m/Y'),
            'usuarioNombre' => auth()->user()?->name ?? 'Administrador',
            'destinatario' => $this->destinatario,
            'observacionesGenerales' => $this->observacionesGenerales,
            'totalAgentes' => count($this->detalles),
            'cantidades' => $cantidades,
            'detalles' => $detallesProcesados,
        ]);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'Informe_Diario_Novedades_'.$this->fecha.'.pdf'
        );
    }

    public function exportarPdf()
    {
        if ($this->esAdmin()) {
            $this->guardarInforme();
        }

        $cantidades = [
            'presente' => 0,
            'licencia' => 0,
            'falta_justificada' => 0,
            'ausente_sin_aviso' => 0,
            'tardanza' => 0,
        ];

        $labels = [
            'presente' => 'Presente',
            'licencia' => 'Licencia',
            'falta_justificada' => 'Falta Justificada',
            'ausente_sin_aviso' => 'Ausente sin Aviso',
            'tardanza' => 'Tardanza',
        ];

        $detallesProcesados = [];
        foreach ($this->detalles as $det) {
            $tipo = $det['tipo_novedad'] ?? 'presente';
            if (isset($cantidades[$tipo])) {
                $cantidades[$tipo]++;
            }

            $detallesProcesados[] = array_merge($det, [
                'tipo_novedad_label' => $labels[$tipo] ?? $tipo,
            ]);
        }

        $pdf = Pdf::loadView('filament.admin.pages.informe-diario-pdf', [
            'fecha' => $this->fecha,
            'fechaFormatted' => Carbon::parse($this->fecha)->format('d/m/Y'),
            'usuarioNombre' => auth()->user()?->name ?? 'Administrador',
            'destinatario' => $this->destinatario,
            'observacionesGenerales' => $this->observacionesGenerales,
            'totalAgentes' => count($this->detalles),
            'cantidades' => $cantidades,
            'detalles' => $detallesProcesados,
        ]);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'Informe_Diario_Novedades_'.$this->fecha.'.pdf'
        );
    }

    public function getCantidadesProperty(): array
    {
        $cantidades = [
            'presente' => 0,
            'licencia' => 0,
            'falta_justificada' => 0,
            'ausente_sin_aviso' => 0,
            'tardanza' => 0,
        ];

        foreach ($this->detalles as $det) {
            $tipo = $det['tipo_novedad'] ?? 'presente';
            if (isset($cantidades[$tipo])) {
                $cantidades[$tipo]++;
            }
        }

        return $cantidades;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
