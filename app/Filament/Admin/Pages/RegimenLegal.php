<?php

namespace App\Filament\Admin\Pages;

use App\Models\Agente;
use App\Models\TipoSolicitud;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class RegimenLegal extends Page
{
    protected string $view = 'filament.admin.pages.regimen-legal';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationLabel = 'Regimen Legal';

    protected static string|\UnitEnum|null $navigationGroup = 'Licencias';

    protected static ?string $title = 'Regimen legal vigente';

    protected static ?string $slug = 'regimen-legal';

    protected static ?int $navigationSort = 11;

    public ?string $categoria = null;

    public string $buscar = '';

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('agente') ?? false;
    }

    public function getViewData(): array
    {
        return [
            'regimenes' => $this->getRegimenes(),
            'categorias' => $this->getCategorias(),
            'pdfUrl' => config('licencias.regimen_legal_pdf_url'),
            'tipoContratoAgente' => $this->getTipoContratoAgenteLabel(),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function getRegimenes(): Collection
    {
        $tipoContratoAgente = Auth::user()?->agente?->tipo_contrato;

        return TipoSolicitud::query()
            ->when(
                filled($this->categoria),
                fn ($query) => $query->where('categoria', $this->categoria)
            )
            ->when(filled($this->buscar), function ($query): void {
                $query->where(function ($subQuery): void {
                    $subQuery
                        ->where('nombre', 'like', '%'.$this->buscar.'%')
                        ->orWhere('articulo_legal', 'like', '%'.$this->buscar.'%')
                        ->orWhere('categoria', 'like', '%'.$this->buscar.'%');
                });
            })
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get()
            ->map(function (TipoSolicitud $tipoSolicitud) use ($tipoContratoAgente): array {
                $duracion = $tipoSolicitud->duracion_maxima_dias
                    ? $tipoSolicitud->duracion_maxima_dias.' dias'
                    : 'Segun normativa aplicable';

                $contratos = $tipoSolicitud->contratosHabilitados()
                    ->map(fn (string $tipo): string => Agente::TIPO_CONTRATO_LABELS[$tipo] ?? $tipo)
                    ->implode(', ');

                return [
                    'nombre' => $tipoSolicitud->nombre,
                    'categoria' => $tipoSolicitud->categoria,
                    'articulo_legal' => $tipoSolicitud->articulo_legal,
                    'aplica_al_agente' => $tipoContratoAgente
                        ? $tipoSolicitud->permiteContrato($tipoContratoAgente)
                        : true,
                    'contenido' => [
                        ['label' => 'Articulo legal', 'value' => $tipoSolicitud->articulo_legal ?: 'Sin articulo asignado'],
                        ['label' => 'Categoria', 'value' => $tipoSolicitud->categoria ?: 'Sin categoria'],
                        ['label' => 'Duracion maxima', 'value' => $duracion],
                        ['label' => 'Unidad de medida', 'value' => $tipoSolicitud->unidad_medida === 'horas' ? 'Horas' : 'Dias'],
                        ['label' => 'Tipo de dias', 'value' => $tipoSolicitud->tipo_dias ? ucfirst($tipoSolicitud->tipo_dias) : 'No especificado'],
                        ['label' => 'Goce de sueldo', 'value' => $tipoSolicitud->goce_sueldo_porcentaje.'%'],
                        ['label' => 'Intervencion medica', 'value' => $tipoSolicitud->requiere_intervencion_medica ? 'Requerida' : 'No requerida'],
                        ['label' => 'Adjunto obligatorio', 'value' => $tipoSolicitud->requiere_adjunto ? 'Si' : 'No'],
                        ['label' => 'Contrataciones habilitadas', 'value' => $contratos],
                    ],
                ];
            });
    }

    /**
     * @return Collection<int, string>
     */
    protected function getCategorias(): Collection
    {
        return TipoSolicitud::query()
            ->whereNotNull('categoria')
            ->orderBy('categoria')
            ->pluck('categoria')
            ->unique()
            ->values();
    }

    protected function getTipoContratoAgenteLabel(): ?string
    {
        $tipoContrato = Auth::user()?->agente?->tipo_contrato;

        if (! $tipoContrato) {
            return null;
        }

        return Agente::TIPO_CONTRATO_LABELS[$tipoContrato] ?? $tipoContrato;
    }
}
