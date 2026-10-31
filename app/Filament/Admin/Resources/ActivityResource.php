<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ActivityResource\Pages;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Auditoría';

    protected static ?string $modelLabel = 'Registro de Auditoría';

    protected static ?string $pluralModelLabel = 'Registros de Auditoría';

    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['administrador', 'secretaria_general', 'presidencia']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('log_name')
                    ->label('Nombre del Log'),
                Forms\Components\TextInput::make('description')
                    ->label('Descripción'),
                Forms\Components\TextInput::make('subject_type')
                    ->label('Tipo de Entidad'),
                Forms\Components\TextInput::make('event')
                    ->label('Evento'),
                Forms\Components\TextInput::make('causer_type')
                    ->label('Tipo de Causante'),
                Forms\Components\KeyValue::make('properties')
                    ->label('Propiedades'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('log_name')
                    ->label('Tipo')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Acción')
                    ->searchable(),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Usuario')
                    ->formatStateUsing(fn ($record) => $record->causer ? $record->causer->name : 'Sistema')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Modelo')
                    ->formatStateUsing(fn (string $state) => class_basename($state))
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('log_name')
                    ->label('Tipo de Auditoría')
                    ->options([
                        'default' => 'Sistema',
                        'auth' => 'Autenticación',
                        'solicitudes' => 'Solicitudes',
                    ]),
                Tables\Filters\SelectFilter::make('causer_id')
                    ->label('Usuario')
                    ->options(fn () => User::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return $query->where('causer_type', User::class)
                            ->where('causer_id', $data['value']);
                    })
                    ->searchable(),
                Tables\Filters\SelectFilter::make('description')
                    ->label('Acción')
                    ->options(fn () => Activity::query()
                        ->select('description')
                        ->distinct()
                        ->pluck('description', 'description')
                        ->toArray()
                    )
                    ->searchable(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        DatePicker::make('desde')
                            ->label('Desde'),
                        DatePicker::make('hasta')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['desde'] ?? null) {
                            $indicators[] = 'Desde: '.Carbon::parse($data['desde'])->format('d/m/Y');
                        }
                        if ($data['hasta'] ?? null) {
                            $indicators[] = 'Hasta: '.Carbon::parse($data['hasta'])->format('d/m/Y');
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([
                // \Filament\Actions\DeleteBulkAction::make(), // No delete for audit logs
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
            'view' => Pages\ViewActivity::route('/{record}'),
        ];
    }
}
