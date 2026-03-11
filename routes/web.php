<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('filament.admin.pages.dashboard'));

require __DIR__.'/settings.php';
