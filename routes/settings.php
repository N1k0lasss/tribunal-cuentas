<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'pages.settings.profile')->name('profile.edit');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Volt::route('settings/password', 'pages.settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'pages.settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'pages.settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
