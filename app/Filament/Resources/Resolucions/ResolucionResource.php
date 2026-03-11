<?php

namespace App\Filament\Resources\Resolucions;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Resolucions\Pages\ListResolucions;
use App\Filament\Resources\Resolucions\Pages\CreateResolucion;
use App\Filament\Resources\Resolucions\Pages\EditResolucion;
use App\Filament\Resources\ResolucionResource\Pages;
use App\Models\Resolucion;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ResolucionResource extends Resource
{
    protected static ?string $model = Resolucion::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationLabel = 'Resoluciones';

    protected static string | \UnitEnum | null $navigationGroup = 'Licencias';

    protected static ?string $pluralModelLabel = 'Resoluciones';

    protected static ?string $modelLabel = 'Resolución';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('numero')
                    ->label('Número')
                    ->required()
                    ->maxLength(255),
                DatePicker::make('fecha_emision')
                    ->label('Fecha de Emisión')
                    ->required(),
                Textarea::make('detalle')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Select::make('solicitud_id')
                    ->label('Solicitud')
                    ->relationship('solicitud', 'id')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fecha_emision')
                    ->label('Fecha Emisión')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('detalle')
                    ->limit(50),
                TextColumn::make('solicitud_id')
                    ->label('Solicitud #')
                    ->sortable(),
            ])
            ->filters([
                //
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
            'index' => ListResolucions::route('/'),
            'create' => CreateResolucion::route('/create'),
            'edit' => EditResolucion::route('/{record}/edit'),
        ];
    }
}
