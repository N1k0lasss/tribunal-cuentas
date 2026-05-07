<?php

namespace App\Filament\Admin\Resources\Agentes\Tables;

use App\Models\Agente;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
                TextColumn::make('tipo_contrato')
                    ->label('Tipo de Contrato')
                    ->badge()
                    ->colors([
                        'success' => fn ($state) => str_contains(strtolower($state ?? ''), 'permanente'),
                        'warning' => fn ($state) => str_contains(strtolower($state ?? ''), 'transitoria'),
                        'gray' => fn ($state) => str_contains(strtolower($state ?? ''), 'locación') || str_contains(strtolower($state ?? ''), 'locacion'),
                    ])
                    ->searchable(),
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
                    ->description(fn ($record) => $record->fecha_ingreso?->diffForHumans() ?? null)
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
                SelectFilter::make('tipo_contrato')
                    ->label('Tipo de Contrato')
                    ->options(function () {
                        return Agente::query()
                            ->distinct()
                            ->whereNotNull('tipo_contrato')
                            ->pluck('tipo_contrato', 'tipo_contrato')
                            ->toArray();
                    }),
                SelectFilter::make('area_id')
                    ->label('Área')
                    ->relationship('area', 'nombre')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('apellido', 'asc')
            ->recordActions([
                ViewAction::make()
                    ->label('')
                    ->tooltip('Ver detalle'),
                EditAction::make()
                    ->label('')
                    ->tooltip('Editar agente'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
