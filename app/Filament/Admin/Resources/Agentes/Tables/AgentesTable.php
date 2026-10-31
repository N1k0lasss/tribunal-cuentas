<?php

namespace App\Filament\Admin\Resources\Agentes\Tables;

use App\Models\Agente;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AgentesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('legajo')
                    ->label('Legajo')
                    ->sortable()
                    ->searchable()
                    ->placeholder('—')
                    ->weight(FontWeight::SemiBold),
                TextColumn::make('apellido')
                    ->label('Agente')
                    ->formatStateUsing(fn ($record) => "{$record->nombre} {$record->apellido}")
                    ->searchable(['apellido', 'nombre'])
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->icon('heroicon-o-user-circle'),
                TextColumn::make('dni')
                    ->label('DNI')
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return '—';
                        }

                        return number_format((int) $state, 0, '', '.');
                    })
                    ->searchable()
                    ->color('gray'),
                TextColumn::make('cargo')
                    ->label('Cargo')
                    ->searchable()
                    ->sortable()
                    ->color('gray')
                    ->placeholder('—'),
                TextColumn::make('situacion_revista')
                    ->label('Situación de Revista')
                    ->searchable(),
                TextColumn::make('estado_legajo')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => match (true) {
                        $state === null => 'Sin datos',
                        (int) $state === 1 => 'Activo',
                        default => 'Baja',
                    })
                    ->color(fn ($state): string => match (true) {
                        $state === null => 'gray',
                        (int) $state === 1 => 'success',
                        default => 'danger',
                    }),
                TextColumn::make('area.nombre')
                    ->label('Área')
                    ->icon('heroicon-o-building-office')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Sin área'),
                TextColumn::make('fecha_ingreso')
                    ->label('Fecha de Ingreso')
                    ->date('d/m/Y')
                    ->sortable()
                    ->description(fn($record) => $record->fecha_ingreso?->diffForHumans() ?? null)
                    ->placeholder('—')
                    ->color('gray'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('situacion_revista')
                    ->label('Situación de Revista')
                    ->options(function () {
                        return Agente::query()
                            ->distinct()
                            /** @phpstan-ignore-next-line */
                            ->whereNotNull('situacion_revista')
                            ->pluck('situacion_revista', 'situacion_revista')
                            ->toArray();
                    }),
                SelectFilter::make('area_id')
                    ->label('Área')
                    ->relationship('area', 'nombre')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('estado_legajo')
                    ->label('Estado del legajo')
                    ->options([
                        1 => 'Activo',
                        0 => 'Baja',
                    ]),
            ])
            ->defaultSort('apellido', 'asc')
            ->recordActions([
                ViewAction::make()
                    ->label('')
                    ->tooltip('Ver detalle'),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
            ])
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
