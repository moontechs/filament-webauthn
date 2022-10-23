<?php

namespace Moontechs\FilamentWebauthn;

use Filament\PluginServiceProvider;
use Livewire\Livewire;
use Moontechs\FilamentWebauthn\Http\Livewire\WebauthnLogin;
use Moontechs\FilamentWebauthn\Http\Livewire\WebauthnRedirectToLoginButton;
use Moontechs\FilamentWebauthn\Http\Livewire\WebauthnRegisterButton;
use Moontechs\FilamentWebauthn\Widgets\WebauthnRegisterWidget;
use Spatie\LaravelPackageTools\Package;

class FilamentWebauthnServiceProvider extends PluginServiceProvider
{
    protected array $scripts = [
        'filament-webauthn-scripts' => __DIR__.'/../resources/assets/dist/filament-webauthn.js',
    ];

    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-webauthn')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoutes('web')
            ->hasTranslations()
            ->hasAssets()
            ->publishesServiceProvider('FilamentWebauthnServiceProvider')
            ->hasMigration('create_filament-webauthn_table');
    }

    public function boot()
    {
        $serviceProvider = parent::boot();
        Livewire::component('webauthn-register-button', WebauthnRegisterButton::class);
        Livewire::component('webauthn-login', WebauthnLogin::class);
        Livewire::component('webauthn-redirect-to-login-button', WebauthnRedirectToLoginButton::class);

        return $serviceProvider;
    }
}
