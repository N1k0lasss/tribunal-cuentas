<?php

namespace App\Filament\Admin\Resources\TipoSolicituds;

use App\Filament\Admin\Resources\TipoSolicituds\Pages\CreateTipoSolicitud;
use App\Filament\Admin\Resources\TipoSolicituds\Pages\EditTipoSolicitud;
use App\Filament\Admin\Resources\TipoSolicituds\Pages\ListTipoSolicituds;
use App\Models\Agente;
use App\Models\TipoSolicitud;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class TipoSolicitudResource extends Resource
{
    protected static ?string $model = TipoSolicitud::class;

    protected static ?string $navigationLabel = 'Régimen Legal';

    protected static UnitEnum|string|null $navigationGroup = 'Licencias';

    protected static ?string $pluralModelLabel = 'Régimenes Legales';

    protected static ?string $modelLabel = 'Régimen Legal';

    public static function getNavigationIcon(): string | BackedEnum | Htmlable | null
    {
        return 'heroicon-o-clipboard-document-list';
    }

    public static function canAccess(): bool
    {
        return ! Auth::user()?->hasRole('agente');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('nombre')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                TextInput::make('categoria')
                    ->label('Categoría')
                    ->maxLength(255),
                CheckboxList::make('contratos_habilitados')
                    ->label('Tipos de contratación habilitados')
                    ->options(Agente::TIPO_CONTRATO_LABELS)
                    ->columns(3)
                    ->default(Agente::TIPOS_CONTRATO)
                    ->required(),
                TextInput::make('articulo_legal')
                    ->label('Artículo Legal')
                    ->maxLength(255),
                TextInput::make('duracion_maxima_dias')
                    ->label('Duración Máxima (días)')
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
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('categoria')
                    ->label('Categoría')
                    ->sortable(),
                TextColumn::make('contratos_habilitados')
                    ->label('Contratos')
                    ->formatStateUsing(function ($state): string {
                        if (! is_array($state) || $state === []) {
                            return 'Todos';
                        }

                        return collect($state)
                            ->map(fn ($item): string => Agente::TIPO_CONTRATO_LABELS[$item] ?? $item)
                            ->implode(', ');
                    })
                    ->wrap(),
                TextColumn::make('articulo_legal')
                    ->label('Artículo Legal'),
                TextColumn::make('duracion_maxima_dias')
                    ->label('Duración Máx. (días)')
                    ->sortable(),
                IconColumn::make('requiere_adjunto')
                    ->label('Req. Adjunto')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
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
