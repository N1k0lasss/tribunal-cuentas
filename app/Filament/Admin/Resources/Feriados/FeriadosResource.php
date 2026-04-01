<?php

namespace App\Filament\Admin\Resources\Feriados;

use App\Filament\Admin\Resources\Feriados\Pages\CreateFeriados;
use App\Filament\Admin\Resources\Feriados\Pages\EditFeriados;
use App\Filament\Admin\Resources\Feriados\Pages\ListFeriados;
use App\Filament\Admin\Resources\Feriados\Schemas\FeriadosForm;
use App\Filament\Admin\Resources\Feriados\Tables\FeriadosTable;
use App\Models\Feriados;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Forms\Form;
//use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FeriadosResource extends Resource
{
    protected static ?string $model = Feriados::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    //protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'Calendario';

   public static function form(Form $form): Form
   {
    return FeriadosForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return FeriadosTable::configure($table);
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
            'index' => ListFeriados::route('/'),
            'create' => CreateFeriados::route('/create'),
            'edit' => EditFeriados::route('/{record}/edit'),
        ];
    }
}
