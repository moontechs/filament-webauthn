<?php

namespace Moontechs\FilamentWebauthn\Auth;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Session;
use MadWizard\WebAuthn\Dom\AttestationConveyancePreference;
use MadWizard\WebAuthn\Exception\WebAuthnException;
use MadWizard\WebAuthn\Json\JsonConverter;
use MadWizard\WebAuthn\Server\Registration\RegistrationOptions;
use MadWizard\WebAuthn\Server\ServerInterface;
use Moontechs\FilamentWebauthn\Exceptions\RegistrationException;
use Moontechs\FilamentWebauthn\Models\WebauthnKey;

class Registrator implements RegistratorInterface
{
    private ServerInterface $server;

    private RegistrationOptions $registrationOptions;

    public function __construct(
        ServerInterface $server,
        RegistrationOptions $registrationOptions
    ) {
        $this->server = $server;
        $this->registrationOptions = $registrationOptions;
    }

    public function getClientOptions(): string
    {
        $this->registrationOptions->setTimeout(config('filament-webauthn.auth.client_options.timeout'));

        if (! empty(config('filament-webauthn.auth.client_options.user_verification'))) {
            $this->registrationOptions->setUserVerification(config('filament-webauthn.auth.client_options.user_verification'));
        }

        if (! empty(config('filament-webauthn.auth.client_options.attestation'))) {
            $this->registrationOptions->setAttestation(AttestationConveyancePreference::DIRECT);
        }

        if (! empty(config('filament-webauthn.auth.client_options.platform'))) {
            $this->registrationOptions->setAuthenticatorAttachment(config('filament-webauthn.auth.client_options.platform'));
        }
        $registrationRequest = $this->server->startRegistration($this->registrationOptions);

        Session::put($this->getSessionKey(), $registrationRequest->getContext());

        return json_encode($registrationRequest->getClientOptionsJson());
    }

    public function validateAndRegister(string $data)
    {
        try {
            $registrationResult = $this->server->finishRegistration(
                JsonConverter::decodeAttestation(json_decode($data, true)),
                Session::get($this->getSessionKey())
            );

            if (! $registrationResult->isUserVerified()) {
                throw new RegistrationException();
            }
            WebauthnKey::create([
                'credential_id' => $registrationResult->getCredentialId()->toString(),
                'user_id' => Filament::auth()->user()->getAuthIdentifier(),
                'public_key' => base64_encode(serialize($registrationResult->getPublicKey())),
                'user_handle' => $registrationResult->getUserHandle()->toString(),
            ]);
            Session::forget($this->getSessionKey());
        } catch (WebAuthnException $exception) {
            throw new RegistrationException($exception->getMessage());
        } catch (\Throwable $throwable) {
            throw new RegistrationException();
        }

        return true;
    }

    private function getSessionKey(): string
    {
        return 'filament:webauthn:register:'.Filament::auth()->user()->getAuthIdentifier();
    }
}
