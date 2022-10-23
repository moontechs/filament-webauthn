<?php

namespace Moontechs\FilamentWebauthn\Http\Livewire;

use Filament\Facades\Filament;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use MadWizard\WebAuthn\Exception\NoCredentialsException;
use Moontechs\FilamentWebauthn\Factories\WebauthnFactory;

/**
 * @property ComponentContainer $form
 */
class WebauthnLogin extends Component implements HasForms
{
    use InteractsWithForms;

    public ?string $email = '';

    public bool $remember = false;

    public string $icon;

    public string $clientOptions = '';

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }
        $this->icon = config('filament-webauthn.login_button.icon', '');

        if (config('check_if_supported')) {
            $this->emitSelf('supported');
        }

        $this->form->fill();
    }

    public function getClientOptions(): void
    {
        $formState = $this->form->getState();
        $this->validate([
            'email' => 'required|email',
        ]);

        try {
            $this->clientOptions = (new WebauthnFactory())
                ->createAuthenticator($formState['email'])
                ->getClientOptions();
        } catch (NoCredentialsException $exception) {
            throw ValidationException::withMessages([
                'email' => __('filament::login.messages.failed'),
            ]);
        }

        $this->emitSelf('clientOptions');
    }

    public function authenticate(string $data): ?LoginResponse
    {
        try {
            (new WebauthnFactory())
                ->createAuthenticator($this->email)
                ->validateAndLogin($data, $this->remember);
        } catch (\Throwable $throwable) {
            throw ValidationException::withMessages([
                'email' => __('filament::login.messages.failed'),
            ])->redirectTo(config('filament-webauthn.login_page_url'));
        }

        return app(LoginResponse::class);
    }

    public function notifyUnsupported(): void
    {
        Notification::make()
            ->title(__('filament-webauthn::filament-webauthn.notifications.authentication.error'))
            ->body(__('filament-webauthn::filament-webauthn.notifications.unsupported'))
            ->danger()
            ->send();
    }

    public function notifyError(string $text): void
    {
        Notification::make()
            ->title(__('filament-webauthn::filament-webauthn.notifications.authentication.error'))
            ->body($text)
            ->danger()
            ->send();
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('email')
                ->label(__('filament::login.fields.email.label'))
                ->email()
                ->required()
                ->autocomplete(),
            Checkbox::make('remember')
                ->label(__('filament::login.fields.remember.label')),
        ];
    }

    public function render(): View
    {
        return view('filament-webauthn::livewire.webauthn-login')
            ->layout('filament::components.layouts.card', [
                'title' => __('filament::login.title'),
            ]);
    }
}
