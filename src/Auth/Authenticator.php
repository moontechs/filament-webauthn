<?php

namespace Moontechs\FilamentWebauthn\Auth;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use MadWizard\WebAuthn\Dom\UserVerificationRequirement;
use MadWizard\WebAuthn\Json\JsonConverter;
use MadWizard\WebAuthn\Server\Authentication\AuthenticationOptions;
use MadWizard\WebAuthn\Server\ServerInterface;
use Moontechs\FilamentWebauthn\Repositories\UserRepository;

class Authenticator implements AuthenticatorInterface
{
    private ServerInterface $server;

    private AuthenticationOptions $authenticationOptions;

    private UserRepository $userRepository;

    public function __construct(
        ServerInterface $server,
        AuthenticationOptions $authenticationOptions,
        UserRepository $userRepository
    ) {
        $this->server = $server;
        $this->authenticationOptions = $authenticationOptions;
        $this->userRepository = $userRepository;
    }

    public function getClientOptions(): string
    {
        if (! empty(config('filament-webauthn.auth.client_options.user_verification'))) {
            $this->authenticationOptions->setUserVerification(UserVerificationRequirement::REQUIRED);
        }
        $this->authenticationOptions->setTimeout(config('filament-webauthn.auth.client_options.timeout'));
        $authenticationRequest = $this->server->startAuthentication($this->authenticationOptions);

        Session::put($this->getSessionKey(
            $this->authenticationOptions->getUserHandle()->toString()),
            $authenticationRequest->getContext()
        );

        return json_encode($authenticationRequest->getClientOptionsJson());
    }

    public function validateAndLogin(string $data, bool $remember = false): bool
    {
        try {
            $authenticationResult = $this->server->finishAuthentication(
                JsonConverter::decodeCredential(json_decode($data, true), 'assertion'),
                Session::get($this->getSessionKey($this->authenticationOptions->getUserHandle()->toString()))
            );

            if (! $authenticationResult->isUserVerified()) {
                return false;
            }
            Filament::auth()->loginUsingId(
                $this->userRepository->getUserIdByCredentialId(
                    base64_decode($this->authenticationOptions->getUserHandle()->toString())
                ),
                $remember
            );
            Session::forget($this->getSessionKey($this->authenticationOptions->getUserHandle()->toString()));

            return true;
        } catch (\Throwable $throwable) {
            Log::error('validation or login failed', [$throwable]);

            return false;
        }
    }

    private function getSessionKey(string $userHandle): string
    {
        return 'filament:webauthn:login:'.$userHandle;
    }
}
