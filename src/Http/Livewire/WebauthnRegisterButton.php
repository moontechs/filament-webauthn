<?php

namespace Moontechs\FilamentWebauthn\Http\Livewire;

use Filament\Notifications\Notification;
use Livewire\Component;
use Moontechs\FilamentWebauthn\Exceptions\RegistrationException;
use Moontechs\FilamentWebauthn\Factories\WebauthnFactory;

class WebauthnRegisterButton extends Component
{
    public string $icon;

    public string $class;

    protected $listeners = ['save'];

    public function mount(): void
    {
        $this->icon = config('filament-webauthn.register_button.icon', '');
        $this->class = config('filament-webauthn.register_button.class', '');

        if (config('check_if_supported')) {
            $this->emitSelf('supported');
        }
    }

    public function render()
    {
        return view('filament-webauthn::livewire.webauthn-register-button');
    }

    public function register(): void
    {
        $this->emitSelf('register', (new WebauthnFactory())->createRegistrator()->getClientOptions());
    }

    public function notifyUnsupported(): void
    {
        Notification::make()
            ->title(__('filament-webauthn::filament-webauthn.notifications.registration.error'))
            ->body(__('filament-webauthn::filament-webauthn.notifications.unsupported'))
            ->danger()
            ->send();
    }

    public function notifyError(string $text): void
    {
        Notification::make()
            ->title(__('filament-webauthn::filament-webauthn.notifications.registration.error'))
            ->body($text)
            ->danger()
            ->send();
    }

    public function validateAndRegister(string $data): void
    {
        try {
            $saveResult = (new WebauthnFactory())->createRegistrator()->validateAndRegister($data);

            if (! $saveResult) {
                Notification::make()
                    ->title(__('filament-webauthn::filament-webauthn.notifications.registration.error'))
                    ->danger()
                    ->send();

                return;
            }
            Notification::make()
                ->title(__('filament-webauthn::filament-webauthn.notifications.registration.success'))
                ->success()
                ->send();
        } catch (RegistrationException $exception) {
            Notification::make()
                ->title(__('filament-webauthn::filament-webauthn.notifications.registration.error'))
                ->body($exception->getMessage())
                ->danger()
                ->send();
        } catch (\Throwable $throwable) {
            Notification::make()
                ->title(__('filament-webauthn::filament-webauthn.notifications.registration.error'))
                ->danger()
                ->send();
        }
    }
}
