<?php

namespace App\Filament\Admin\Resources\Resolucions;

use App\Filament\Admin\Resources\Resolucions\Pages\CreateResolucion;
use App\Filament\Admin\Resources\Resolucions\Pages\EditResolucion;
use App\Filament\Admin\Resources\Resolucions\Pages\ListResolucions;
use App\Models\Resolucion;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ResolucionResource extends Resource
{
    protected static ?string $model = Resolucion::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationLabel = 'Resoluciones';

    protected static ?string $navigationGroup = 'Licencias';

    protected static ?string $pluralModelLabel = 'Resoluciones';

    protected static ?string $modelLabel = 'Resolución';

    public static function canAccess(): bool
    {
        return ! Auth::user()?->hasRole('agente');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('numero')
                    ->label('Número')
                    ->required()
                    ->maxLength(255),
                DatePicker::make('fecha_emision')
                    ->label('Fecha de Emisión')
                    ->required(),
                Textarea::make('detalle')
                    ->label('Detalle')
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
