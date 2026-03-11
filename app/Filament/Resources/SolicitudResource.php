<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudResource\Pages;
use App\Models\Solicitud;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SolicitudResource extends Resource
{
    protected static ?string $model = Solicitud::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Solicitudes';

    protected static ?string $navigationGroup = 'Licencias';

    protected static ?string $pluralModelLabel = 'Solicitudes';

    protected static ?string $modelLabel = 'Solicitud';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('agente_id')
                    ->label('Agente')
                    ->relationship('agente', 'apellido')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->apellido}, {$record->nombre}")
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('tipo_solicitud_id')
                    ->label('Tipo de Solicitud')
                    ->relationship('tipoSolicitud', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('fecha_solicitud')
                    ->label('Fecha de Solicitud')
                    ->required(),
                Forms\Components\DatePicker::make('fecha_inicio')
                    ->label('Fecha Inicio')
                    ->required(),
                Forms\Components\DatePicker::make('fecha_fin')
                    ->label('Fecha Fin')
                    ->required(),
                Forms\Components\TextInput::make('dias_solicitados')
                    ->label('Días Solicitados')
                    ->numeric()
                    ->required(),
                Forms\Components\Textarea::make('motivo')
                    ->label('Motivo')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Select::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'aprobada' => 'Aprobada',
                        'rechazada' => 'Rechazada',
                    ])
                    ->default('pendiente')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('agente.apellido')
                    ->label('Agente')
                    ->formatStateUsing(fn ($record) => "{$record->agente->apellido}, {$record->agente->nombre}")
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipoSolicitud.nombre')
                    ->label('Tipo')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_solicitud')
                    ->label('Fecha Solicitud')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Desde')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_fin')
                    ->label('Hasta')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('dias_solicitados')
                    ->label('Días')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'warning' => 'pendiente',
                        'success' => 'aprobada',
                        'danger' => 'rechazada',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'aprobada' => 'Aprobada',
                        'rechazada' => 'Rechazada',
                    ]),
                Tables\Filters\SelectFilter::make('tipo_solicitud_id')
                    ->label('Tipo de Solicitud')
                    ->relationship('tipoSolicitud', 'nombre'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSolicituds::route('/'),
            'create' => Pages\CreateSolicitud::route('/create'),
            'edit' => Pages\EditSolicitud::route('/{record}/edit'),
        ];
    }
}
