<?php

namespace Moontechs\FilamentWebauthn\Http\Livewire;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Livewire\Component;

class WebauthnRedirectToLoginButton extends Component
{
    public string $icon;

    public function mount(): void
    {
        $this->icon = config('filament-webauthn.login_button.icon', '');
    }

    public function render()
    {
        return view('filament-webauthn::livewire.webauthn-redirect-to-login-button');
    }

    public function redirectToLoginPage(): RedirectResponse|Redirector
    {
        return redirect(config('filament-webauthn.login_page_url', '/'));
    }
}
