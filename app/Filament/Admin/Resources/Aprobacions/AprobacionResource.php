<?php

namespace App\Filament\Admin\Resources\Aprobacions;

use App\Filament\Admin\Resources\Aprobacions\Pages\CreateAprobacion;
use App\Filament\Admin\Resources\Aprobacions\Pages\EditAprobacion;
use App\Filament\Admin\Resources\Aprobacions\Pages\ListAprobacions;
use App\Models\Aprobacion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class AprobacionResource extends Resource
{
    protected static ?string $model = Aprobacion::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'Aprobaciones';

    protected static string|\UnitEnum|null $navigationGroup = 'Licencias';

    protected static ?string $pluralModelLabel = 'Aprobaciones';

    protected static ?string $modelLabel = 'Aprobación';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('solicitud_id')
                    ->label('Solicitud')
                    ->relationship('solicitud', 'id')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('aprobado_por')
                    ->label('Aprobado por')
                    ->relationship('aprobador', 'apellido')
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->apellido}, {$record->nombre}")
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('etapa')
                    ->maxLength(255),
                Select::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                    ])
                    ->default('pendiente')
                    ->required(),
                Textarea::make('observacion')
                    ->label('Observación')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                DatePicker::make('fecha')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('solicitud_id')
                    ->label('Solicitud #')
                    ->sortable(),
                TextColumn::make('aprobador.apellido')
                    ->label('Aprobado por')
                    ->visible(function (): bool {
                        $puedeVisualizarAprobaciones = Auth::user()
                            ->hasAnyRole(['secretaria_general', 'jefatura', 'administrador', 'presidencia']);

                        return $puedeVisualizarAprobaciones;
                    })
                    ->formatStateUsing(fn($record) => $record->aprobador ? "{$record->aprobador->apellido}, {$record->aprobador->nombre}" : '-')
                    ->sortable(),
                TextColumn::make('etapa')
                    ->sortable(),
                TextColumn::make('estado')
                    ->badge()
                    ->colors([
                        'warning' => 'pendiente',
                        'success' => 'aprobado',
                        'danger' => 'rechazado',
                    ]),
                TextColumn::make('fecha')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['aprobador', 'solicitud']);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        /** @var Builder<Aprobacion> $query */
        $query = parent::getEloquentQuery();

        $tienePermisosDeAdmin = Auth::user()?->hasAnyRole(['agente', 'secretaria_general', 'presidencia']) ?? false;

        if (!$tienePermisosDeAdmin) {
            $query->whereHas('solicitud', function (Builder $query): void {
                $query->where('agente_id', Auth::user()->id);
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAprobacions::route('/'),
            'create' => CreateAprobacion::route('/create'),
            'edit' => EditAprobacion::route('/{record}/edit'),
        ];
    }
}
