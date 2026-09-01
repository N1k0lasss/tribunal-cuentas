<?php

namespace App\Filament\Admin\Resources\Areas;

use App\Filament\Admin\Resources\Areas\Pages\ListAreas;
use App\Filament\Admin\Resources\Areas\Tables\AreasTable;
use App\Models\Area;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use BackedEnum;
use Illuminate\Contracts\Support\Htmlable;

class AreaResource extends Resource
{
    protected static ?string $model = Area::class;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function getNavigationIcon(): string | BackedEnum | Htmlable | null
    {
        return 'heroicon-o-rectangle-stack';
    }

    protected static string|\UnitEnum|null $navigationGroup = 'Gestión de personal';

    protected static ?string $navigationLabel = 'Areas';

    protected static ?int $navigationSort = 4;

    protected static ?string $pluralModelLabel = 'Áreas';

    protected static ?string $modelLabel = 'Área';


    public static function table(Table $table): Table
    {
        return AreasTable::configure($table);
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
            'index' => ListAreas::route('/'),
        ];
    }
}
