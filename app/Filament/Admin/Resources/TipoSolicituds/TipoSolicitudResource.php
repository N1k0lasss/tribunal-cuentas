<?php

namespace App\Filament\Admin\Resources\TipoSolicituds;

use App\Filament\Admin\Resources\TipoSolicituds\Pages\CreateTipoSolicitud;
use App\Filament\Admin\Resources\TipoSolicituds\Pages\EditTipoSolicitud;
use App\Filament\Admin\Resources\TipoSolicituds\Pages\ListTipoSolicituds;
use App\Models\TipoSolicitud;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TipoSolicitudResource extends Resource
{
    protected static ?string $model = TipoSolicitud::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Régimen Legal';

    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 3;

    protected static ?string $pluralModelLabel = 'Régimenes Legales';

    protected static ?string $modelLabel = 'Régimen Legal';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),
                TextInput::make('categoria')
                    ->label('Categoría')
                    ->maxLength(255),
                TextInput::make('articulo_legal')
                    ->label('Artículo Legal')
                    ->maxLength(255),
                TextInput::make('duracion_maxima_dias')
                    ->label('Duración Máxima (días)')
                    ->numeric(),
                TextInput::make('antiguedad_minima_meses')
                    ->label('Antigüedad Mínima (meses)')
                    ->numeric(),
                Toggle::make('requiere_adjunto')
                    ->label('Requiere Adjunto'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('categoria')
                    ->label('Categoría')
                    ->sortable(),
                TextColumn::make('articulo_legal')
                    ->label('Artículo Legal'),
                TextColumn::make('duracion_maxima_dias')
                    ->label('Duración Máx. (días)')
                    ->sortable(),
                TextColumn::make('antiguedad_minima_meses')
                    ->label('Antigüedad Mín. (meses)')
                    ->sortable(),
                IconColumn::make('requiere_adjunto')
                    ->label('Req. Adjunto')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
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
            'index' => ListTipoSolicituds::route('/'),
            'create' => CreateTipoSolicitud::route('/create'),
            'edit' => EditTipoSolicitud::route('/{record}/edit'),
        ];
    }
}
