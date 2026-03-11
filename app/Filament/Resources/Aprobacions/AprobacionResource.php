<?php

namespace App\Filament\Resources\Aprobacions;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Aprobacions\Pages\ListAprobacions;
use App\Filament\Resources\Aprobacions\Pages\CreateAprobacion;
use App\Filament\Resources\Aprobacions\Pages\EditAprobacion;
use App\Filament\Resources\AprobacionResource\Pages;
use App\Models\Aprobacion;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AprobacionResource extends Resource
{
    protected static ?string $model = Aprobacion::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'Aprobaciones';

    protected static string | \UnitEnum | null $navigationGroup = 'Licencias';

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
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->apellido}, {$record->nombre}")
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('etapa')
                    ->maxLength(255),
                Select::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'aprobada' => 'Aprobada',
                        'rechazada' => 'Rechazada',
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
                    ->formatStateUsing(fn ($record) => $record->aprobador ? "{$record->aprobador->apellido}, {$record->aprobador->nombre}" : '-')
                    ->sortable(),
                TextColumn::make('etapa')
                    ->sortable(),
                BadgeColumn::make('estado')
                    ->colors([
                        'warning' => 'pendiente',
                        'success' => 'aprobada',
                        'danger' => 'rechazada',
                    ]),
                TextColumn::make('fecha')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'aprobada' => 'Aprobada',
                        'rechazada' => 'Rechazada',
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

    public static function getRelations(): array
    {
        return [
            //
        ];
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
