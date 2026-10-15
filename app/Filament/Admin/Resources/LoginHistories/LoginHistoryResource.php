<?php

namespace App\Filament\Admin\Resources\LoginHistories;

use App\Filament\Admin\Resources\LoginHistories\Pages\ListLoginHistories;
use App\Filament\Admin\Resources\LoginHistories\Tables\LoginHistoriesTable;
use App\Models\LoginHistory;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use BackedEnum;
use Illuminate\Contracts\Support\Htmlable;

class LoginHistoryResource extends Resource
{
    protected static ?string $model = LoginHistory::class;

    protected static ?string $recordTitleAttribute = 'user.name';

    public static function canAccess(): bool
    {
        return ! Auth::user()?->hasRole('agente');
    }

    public static function getNavigationIcon(): string | BackedEnum | Htmlable | null
    {
        return 'heroicon-o-shield-check';
    }

    protected static string|\UnitEnum|null $navigationGroup = 'Auditoría';

    protected static ?string $navigationLabel = 'Historial de Logueos';

    protected static ?int $navigationSort = 1;

    protected static ?string $pluralModelLabel = 'Historiales de Logueo';

    protected static ?string $modelLabel = 'Historial de Logueo';

    public static function table(Table $table): Table
    {
        return LoginHistoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoginHistories::route('/'),
        ];
    }
}
