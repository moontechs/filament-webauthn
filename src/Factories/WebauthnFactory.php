<?php

namespace Moontechs\FilamentWebauthn\Factories;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Config;
use MadWizard\WebAuthn\Builder\ServerBuilder;
use MadWizard\WebAuthn\Config\RelyingParty;
use MadWizard\WebAuthn\Credential\UserHandle;
use MadWizard\WebAuthn\Server\Authentication\AuthenticationOptions;
use MadWizard\WebAuthn\Server\Registration\RegistrationOptions;
use MadWizard\WebAuthn\Server\ServerInterface;
use MadWizard\WebAuthn\Server\UserIdentity;
use Moontechs\FilamentWebauthn\Auth\Authenticator;
use Moontechs\FilamentWebauthn\Auth\AuthenticatorInterface;
use Moontechs\FilamentWebauthn\Auth\Registrator;
use Moontechs\FilamentWebauthn\Auth\RegistratorInterface;
use Moontechs\FilamentWebauthn\Auth\User;
use Moontechs\FilamentWebauthn\Auth\UserInterface;
use Moontechs\FilamentWebauthn\Repositories\PublicKeyRepository;
use Moontechs\FilamentWebauthn\Repositories\UserRepository;

class WebauthnFactory
{
    public function createRegistrator(): RegistratorInterface
    {
        return new Registrator(
            $this->createServer(),
            $this->createRegistrationOptions(
                $this->createUserIdentity()
            ),
            Filament::auth()->user()
        );
    }

    public function createAuthenticator(string $loginId): AuthenticatorInterface
    {
        return new Authenticator(
            $this->createServer(),
            $this->createAuthenticationOptions(
                $this->createUserHandle($loginId)
            ),
            new UserRepository()
        );
    }

    public function createUserIdentity(): UserIdentity
    {
        $userName = $this->createUser()->getUserLoginIdentificator();

        return new UserIdentity(
            UserHandle::fromString(base64_encode($userName)),
            $userName,
            $userName
        );
    }

    public function createUserHandle(string $loginId): UserHandle
    {
        return UserHandle::fromString(
            base64_encode($loginId)
        );
    }

    public function createRegistrationOptions(UserIdentity $user): RegistrationOptions
    {
        $registrationOptions = RegistrationOptions::createForUser($user);
        $registrationOptions->setExcludeExistingCredentials(true);

        return $registrationOptions;
    }

    public function createAuthenticationOptions(UserHandle $user): AuthenticationOptions
    {
        return AuthenticationOptions::createForUser($user);
    }

    public function createServer(): ServerInterface
    {
        $relyingParty = new RelyingParty(
            Config::get('filament-webauthn.auth.relying_party.name'),
            Config::get('filament-webauthn.auth.relying_party.origin')
        );
        $relyingParty->setId(Config::get('filament-webauthn.auth.relying_party.id'));

        return (new ServerBuilder())
            ->setRelyingParty($relyingParty)
            ->setCredentialStore(new PublicKeyRepository())
            ->build();
    }

    protected function createUser(): UserInterface
    {
        return new User();
    }
}
