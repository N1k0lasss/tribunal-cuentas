<?php

namespace App\Filament\Admin\Resources\Areas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class AreaForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema ([
                TextInput::make('nombre')
                    ->required(),
            ]);
    }
}
