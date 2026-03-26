<?php

namespace App\Filament\Admin\Resources\Aprobacions;

use App\Filament\Admin\Resources\Aprobacions\Pages;
use App\Models\Aprobacion;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AprobacionResource extends Resource
{
    protected static ?string $model = Aprobacion::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'Aprobaciones';

    protected static string|\UnitEnum|null $navigationGroup = 'Licencias';

    protected static ?string $pluralModelLabel = 'Aprobaciones';

    protected static ?string $modelLabel = 'Aprobación';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('solicitud_id')
                    ->label('Solicitud')
                    ->relationship('solicitud', 'id')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('aprobado_por')
                    ->label('Aprobado por')
                    ->relationship('aprobador', 'apellido')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->apellido} {$record->nombre}")
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('etapa')
                    ->label('Etapa')
                    ->maxLength(255),

                Select::make('estado')
                    ->label('Estado')
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

                DatePicker::make('fecha'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('solicitud.id')->label('Solicitud'),
                TextColumn::make('aprobador.apellido')->label('Aprobador'),
                TextColumn::make('etapa')->label('Etapa'),
                TextColumn::make('estado')->badge(),
                TextColumn::make('fecha')->date()->label('Fecha'),
            ])
            ->filters([])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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