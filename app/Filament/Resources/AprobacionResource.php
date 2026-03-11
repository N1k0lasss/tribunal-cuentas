<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AprobacionResource\Pages;
use App\Models\Aprobacion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AprobacionResource extends Resource
{
    protected static ?string $model = Aprobacion::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'Aprobaciones';

    protected static ?string $navigationGroup = 'Licencias';

    protected static ?string $pluralModelLabel = 'Aprobaciones';

    protected static ?string $modelLabel = 'Aprobación';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('solicitud_id')
                    ->label('Solicitud')
                    ->relationship('solicitud', 'id')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('aprobado_por')
                    ->label('Aprobado por')
                    ->relationship('aprobador', 'apellido')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->apellido}, {$record->nombre}")
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('etapa')
                    ->maxLength(255),
                Forms\Components\Select::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'aprobada' => 'Aprobada',
                        'rechazada' => 'Rechazada',
                    ])
                    ->default('pendiente')
                    ->required(),
                Forms\Components\Textarea::make('observacion')
                    ->label('Observación')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('fecha')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('solicitud_id')
                    ->label('Solicitud #')
                    ->sortable(),
                Tables\Columns\TextColumn::make('aprobador.apellido')
                    ->label('Aprobado por')
                    ->formatStateUsing(fn ($record) => $record->aprobador ? "{$record->aprobador->apellido}, {$record->aprobador->nombre}" : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('etapa')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'warning' => 'pendiente',
                        'success' => 'aprobada',
                        'danger' => 'rechazada',
                    ]),
                Tables\Columns\TextColumn::make('fecha')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'aprobada' => 'Aprobada',
                        'rechazada' => 'Rechazada',
                    ]),
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
            'index' => Pages\ListAprobacions::route('/'),
            'create' => Pages\CreateAprobacion::route('/create'),
            'edit' => Pages\EditAprobacion::route('/{record}/edit'),
        ];
    }
}
