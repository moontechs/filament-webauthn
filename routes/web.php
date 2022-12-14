<?php

use Illuminate\Support\Facades\Route;

Route::domain(config('filament.domain'))
    ->middleware(config('filament.middleware.base'))
    ->prefix(config('filament.path'))
    ->name('filament-webauthn.')
    ->group(function (): void {
        Route::get('/webauthn-login', \Moontechs\FilamentWebauthn\Http\Livewire\WebauthnLogin::class)
            ->name('login');
    });
