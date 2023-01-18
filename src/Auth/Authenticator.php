<?php

namespace Moontechs\FilamentWebauthn\Auth;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use MadWizard\WebAuthn\Exception\WebAuthnException;
use MadWizard\WebAuthn\Json\JsonConverter;
use MadWizard\WebAuthn\Server\Authentication\AuthenticationOptions;
use MadWizard\WebAuthn\Server\ServerInterface;
use Moontechs\FilamentWebauthn\Exceptions\LoginException;
use Moontechs\FilamentWebauthn\Repositories\UserRepositoryInterface;

class Authenticator implements AuthenticatorInterface
{
    private ServerInterface $server;

    private AuthenticationOptions $authenticationOptions;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        ServerInterface $server,
        AuthenticationOptions $authenticationOptions,
        UserRepositoryInterface $userRepository
    ) {
        $this->server = $server;
        $this->authenticationOptions = $authenticationOptions;
        $this->userRepository = $userRepository;
    }

    public function getClientOptions(): string
    {
        try {
            if (! empty(Config::get('filament-webauthn.auth.client_options.user_verification'))) {
                $this->authenticationOptions->setUserVerification(Config::get('filament-webauthn.auth.client_options.user_verification'));
            }
            $this->authenticationOptions->setTimeout(Config::get('filament-webauthn.auth.client_options.timeout'));
            $authenticationRequest = $this->server->startAuthentication($this->authenticationOptions);

            Session::put(
                $this->getSessionKey(
                    $this->authenticationOptions->getUserHandle()->toString()
                ),
                $authenticationRequest->getContext()
            );

            return json_encode($authenticationRequest->getClientOptionsJson());
        } catch (\Throwable $exception) {
            throw new LoginException();
        }
    }

    public function validateAndLogin(string $data, bool $remember = false)
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
        } catch (WebAuthnException $exception) {
            throw new LoginException($exception->getMessage());
        } catch (\Throwable $throwable) {
            throw new LoginException();
        }
    }

    private function getSessionKey(string $userHandle): string
    {
        return 'filament:webauthn:login:'.$userHandle;
    }
}
