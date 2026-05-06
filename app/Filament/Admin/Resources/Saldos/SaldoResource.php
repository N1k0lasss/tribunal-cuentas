<?php

namespace App\Filament\Admin\Resources\Saldos;

use App\Filament\Admin\Resources\Saldos\Pages\CreateSaldo;
use App\Filament\Admin\Resources\Saldos\Pages\EditSaldo;
use App\Filament\Admin\Resources\Saldos\Pages\ListSaldos;
use App\Models\Agente;
use App\Models\Saldo;
use App\Models\TipoSolicitud;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;


class SaldoResource extends Resource
{
    protected static ?string $model = Saldo::class;

    protected static ?string $navigationLabel = 'Saldos';

    protected static UnitEnum|string|null $navigationGroup = 'Gestión de Personal';

    public static function getNavigationIcon(): string | BackedEnum | Htmlable | null
    {
        return 'heroicon-o-calculator';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('anio')
                    ->label('Año')
                    ->numeric()
                    ->required(),
                Select::make('agente_id')
                    ->label('Agente')
                    ->searchable()
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->apellido}, {$record->nombre}")
                    ->options(function ($get) {
                        $anio = $get('anio');

                        $query = Agente::query();

                        if ($anio) {
                            // Mostrar solo agentes que no tengan saldo para el año seleccionado,
                            // o mostrar todos si ya existen (ayuda a evitar duplicados)
                            $query->leftJoin('saldos', function ($join) use ($anio) {
                                $join->on('agentes.id', '=', 'saldos.agente_id')
                                    ->where('saldos.anio', $anio);
                            })->selectRaw('agentes.*, saldos.id as saldo_id');
                        }

                        return $query->get()->mapWithKeys(fn ($a) => [$a->id => "{$a->apellido}, {$a->nombre}"])->toArray();
                    })
                    ->required(),
                Select::make('tipo_saldo')
                    ->label('Tipo de Saldo')
                    ->options(TipoSolicitud::pluck('nombre', 'nombre'))
                    ->searchable()
                    ->required()
                    ->unique(
                        modifyRuleUsing: function (Unique $rule, Get $get) {
                            return $rule
                                ->where('agente_id', $get('agente_id'))
                                ->where('anio', $get('anio'));
                        },
                        ignoreRecord: true
                    )
                    ->validationMessages([
                        'unique' => 'Ya existe un saldo de este tipo para el agente y año seleccionados.',
                    ]),
                TextInput::make('dias_otorgados')
                    ->label('Días Otorgados')
                    ->numeric()
                    ->required(),
                TextInput::make('dias_usados')
                    ->label('Días Usados')
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('agente.apellido')
                    ->label('Agente')
                    ->formatStateUsing(fn ($record) => "{$record->agente->apellido}, {$record->agente->nombre}")
                    ->searchable()
                    ->sortable(),
                TextColumn::make('anio')
                    ->label('Año')
                    ->sortable(),
                TextColumn::make('tipo_saldo')
                    ->label('Tipo')
                    ->sortable(),
                TextColumn::make('dias_otorgados')
                    ->label('Otorgados')
                    ->sortable(),
                TextColumn::make('dias_usados')
                    ->label('Usados')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['agente']);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSaldos::route('/'),
            'create' => CreateSaldo::route('/create'),
            'edit' => EditSaldo::route('/{record}/edit'),
        ];
    }
}
