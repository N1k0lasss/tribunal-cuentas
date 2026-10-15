<?php

namespace App\Filament\Admin\Resources\Agentes\Schemas;

use App\Exceptions\LegajoApiException;
use App\Models\Agente;
use App\Services\LegajoApiService;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class AgenteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('nombre'),
                TextEntry::make('apellido'),
                TextEntry::make('dni'),
                TextEntry::make('cargo')
                    ->placeholder('-'),
                TextEntry::make('situacion_revista')
                    ->placeholder('-'),
                TextEntry::make('area_id')
                    ->numeric(),
                TextEntry::make('fecha_ingreso')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                static::seccionLegajo(),
            ]);
    }

    /**
     * Datos del Sistema de Legajos obtenidos en vivo por API
     * (ver integracion-legajos-api.md). El legajo es la fuente de verdad:
     * esta sección es de solo lectura.
     */
    private static function seccionLegajo(): Section
    {
        return Section::make('Datos del Legajo (en vivo)')
            ->description('Obtenidos de la API del Sistema de Legajos al abrir esta ficha. Fuente de verdad: no se edita desde este sistema.')
            ->icon('heroicon-o-cloud-arrow-down')
            ->columnSpanFull()
            ->columns(['default' => 2, 'lg' => 3])
            ->schema([
                TextEntry::make('legajo_aviso')
                    ->label('Aviso')
                    ->state(fn (Agente $record): ?string => static::aviso(static::consulta($record)))
                    ->color(fn (Agente $record): string => static::consulta($record)['estado'] === 'error' ? 'danger' : 'warning')
                    ->hidden(fn (Agente $record): bool => static::consulta($record)['estado'] === 'ok')
                    ->columnSpanFull(),

                TextEntry::make('legajo_num_legajo')
                    ->label('N° de legajo')
                    ->state(fn (Agente $record) => static::dato($record, 'num_legajo'))
                    ->placeholder('-'),

                TextEntry::make('legajo_categoria')
                    ->label('Categoría')
                    ->state(fn (Agente $record) => static::dato($record, 'categoria'))
                    ->helperText(fn (Agente $record): ?string => static::dato($record, 'categoria_descripcion'))
                    ->placeholder('-'),

                TextEntry::make('legajo_cargo')
                    ->label('Cargo')
                    ->state(fn (Agente $record) => static::dato($record, 'cargo'))
                    ->placeholder('-'),

                TextEntry::make('legajo_area')
                    ->label('Área')
                    ->state(fn (Agente $record) => static::dato($record, 'area'))
                    ->placeholder('-'),

                TextEntry::make('legajo_fecha_ingreso')
                    ->label('Fecha de ingreso')
                    ->state(fn (Agente $record) => static::dato($record, 'fecha_ingreso'))
                    ->date('d/m/Y')
                    ->placeholder('-'),

                TextEntry::make('legajo_antiguedad')
                    ->label('Antigüedad')
                    ->state(fn (Agente $record): ?string => static::antiguedad($record))
                    ->placeholder('-'),

                TextEntry::make('legajo_estado')
                    ->label('Estado del legajo')
                    ->state(fn (Agente $record) => static::dato($record, 'estado'))
                    ->badge()
                    ->formatStateUsing(fn ($state): ?string => filled($state) ? ($state == 1 ? 'Activo' : 'Inactivo') : null)
                    ->color(fn ($state): string => $state == 1 ? 'success' : 'danger')
                    ->placeholder('-'),

                TextEntry::make('legajo_baja')
                    ->label('Baja')
                    ->state(fn (Agente $record): ?string => static::textoBaja($record))
                    ->color('danger')
                    ->columnSpanFull()
                    ->placeholder('-'),

                TextEntry::make('legajo_cuil')
                    ->label('CUIL')
                    ->state(fn (Agente $record) => static::dato($record, 'cuil'))
                    ->placeholder('-'),

                TextEntry::make('legajo_email')
                    ->label('Email institucional')
                    ->state(fn (Agente $record) => static::dato($record, 'email'))
                    ->placeholder('-'),
            ]);
    }

    /**
     * Consulta (memorizada en el servicio: 30 s las respuestas, 10 s los
     * errores) con manejo de errores de la API.
     *
     * @return array{estado: 'ok'|'sin-legajo'|'no-existe'|'error', datos: array<string, mixed>|null, error: string|null, dni: string}
     */
    private static function consulta(Agente $record): array
    {
        $dni = (string) $record->dni;

        try {
            $datos = app(LegajoApiService::class)->datosBasicos($dni);
        } catch (LegajoApiException $e) {
            return ['estado' => 'error', 'datos' => null, 'error' => $e->getMessage(), 'dni' => $dni];
        }

        if ($datos === null) {
            return ['estado' => 'no-existe', 'datos' => null, 'error' => null, 'dni' => $dni];
        }

        return [
            'estado' => ($datos['con_legajo_activo'] ?? false) ? 'ok' : 'sin-legajo',
            'datos' => $datos,
            'error' => null,
            'dni' => $dni,
        ];
    }

    private static function dato(Agente $record, string $campo): mixed
    {
        return static::consulta($record)['datos'][$campo] ?? null;
    }

    /**
     * Motivo y fecha de la baja del legajo, para mostrar en la ficha.
     */
    private static function textoBaja(Agente $record): ?string
    {
        $motivo = static::dato($record, 'motivo_baja');
        $fecha = static::dato($record, 'fecha_baja');

        $partes = array_filter([
            filled($motivo) ? (string) $motivo : null,
            filled($fecha) ? 'baja '.Carbon::parse($fecha)->format('d/m/Y') : null,
        ]);

        return $partes === [] ? null : implode(' · ', $partes);
    }

    private static function antiguedad(Agente $record): ?string
    {
        $fecha = static::dato($record, 'fecha_ingreso');

        if ($fecha === null) {
            return null;
        }

        $fecha = Carbon::parse($fecha);

        return sprintf(
            '%d años (%d meses)',
            (int) $fecha->diffInYears(now()),
            (int) $fecha->diffInMonths(now()),
        );
    }

    private static function aviso(array $consulta): ?string
    {
        return match ($consulta['estado']) {
            // El servicio ya entrega un mensaje apto para mostrar (sin URL ni datos internos).
            'error' => $consulta['error'],
            'no-existe' => 'No se encontró en el Sistema de Legajos ninguna persona con DNI '.$consulta['dni'].'.',
            'sin-legajo' => 'La persona existe en el Legajo pero no tiene legajo activo: no hay cargo, categoría ni fecha de ingreso que mostrar.',
            default => null,
        };
    }
}
