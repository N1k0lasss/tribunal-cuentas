<?php

namespace App\Filament\Admin\Resources\Resolucions;

use App\Filament\Admin\Resources\Resolucions\Pages\CreateResolucion;
use App\Filament\Admin\Resources\Resolucions\Pages\EditResolucion;
use App\Filament\Admin\Resources\Resolucions\Pages\ListResolucions;
use App\Models\Resolucion;
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
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Illuminate\Support\Facades\Auth;
use BackedEnum;
use UnitEnum;
use Illuminate\Contracts\Support\Htmlable;

class ResolucionResource extends Resource
{
    protected static ?string $model = Resolucion::class;

    protected static ?string $navigationLabel = 'Resoluciones';

    protected static UnitEnum|string|null $navigationGroup = 'Flujo de licencias';

    protected static ?int $navigationSort = 5;

    protected static ?string $pluralModelLabel = 'Resoluciones';

    protected static ?string $modelLabel = 'Resolución';

    public static function canAccess(): bool
    {
        return ! Auth::user()?->hasRole('agente');
    }

    public static function getNavigationIcon(): string | BackedEnum | Htmlable | null
    {
        return 'heroicon-o-document-check';
    }

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
                    ->label('Detalle')
                    ->limit(50),
                TextColumn::make('solicitud_id')
                    ->label('Solicitud #')
                    ->sortable(),
            ])
            ->groups([
                Group::make('numero')
                    ->label('Número de Resolución')
                    ->collapsible(),
            ])
            ->defaultGroup('numero')
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
            'index' => ListResolucions::route('/'),
            'create' => CreateResolucion::route('/create'),
            'edit' => EditResolucion::route('/{record}/edit'),
        ];
    }
}
