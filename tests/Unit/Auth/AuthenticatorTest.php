<?php

namespace Moontechs\FilamentWebauthn\Tests\Unit\Auth;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use MadWizard\WebAuthn\Builder\ServerBuilder;
use MadWizard\WebAuthn\Config\RelyingParty;
use MadWizard\WebAuthn\Credential\CredentialId;
use MadWizard\WebAuthn\Credential\CredentialStoreInterface;
use MadWizard\WebAuthn\Credential\UserCredential;
use MadWizard\WebAuthn\Credential\UserHandle;
use MadWizard\WebAuthn\Server\Authentication\AuthenticationContext;
use MadWizard\WebAuthn\Server\Authentication\AuthenticationOptions;
use MadWizard\WebAuthn\Server\ServerInterface;
use Mockery\MockInterface;
use Moontechs\FilamentWebauthn\Auth\Authenticator;
use Moontechs\FilamentWebauthn\Auth\AuthenticatorInterface;
use Moontechs\FilamentWebauthn\Repositories\UserRepositoryInterface;
use Moontechs\FilamentWebauthn\Tests\TestCase;

class AuthenticatorTest extends TestCase
{
    public function testGetClientOptions()
    {
        Config::shouldReceive('get')->with('filament-webauthn.auth.relying_party.name')->andReturn(1);
        Config::shouldReceive('get')->with('filament-webauthn.auth.relying_party.id')->andReturn(1);
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.user_verification')->andReturn('');
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.timeout')->andReturn(60);
        $this->configureSession();

        $clientOptions = $this->createAuthenticator('admin@laravel.test')->getClientOptions();
        $clientOptions = json_decode($clientOptions, true);

        $this->assertEquals(86, strlen($clientOptions['challenge']));
        $this->assertEquals(60, $clientOptions['timeout']);
        $this->assertEquals('laravel.test', $clientOptions['rpId']);
        $this->assertIsArray($clientOptions['allowCredentials']);
        $this->assertEqualsCanonicalizing([
            'type' => 'public-key',
            'id' => 'aWQ',
        ], $clientOptions['allowCredentials'][0]);

        $authenticationContext = $this->getAuthenticationContextFromSession();
        $this->assertEquals('laravel.test', $authenticationContext->getRpId());
        $this->assertFalse($authenticationContext->isUserVerificationRequired());
        $this->assertTrue($authenticationContext->isUserPresenceRequired());
        $this->assertEquals('https', $authenticationContext->getOrigin()->getScheme());
        $this->assertEquals('laravel.test', $authenticationContext->getOrigin()->getHost());
        $this->assertEquals(443, $authenticationContext->getOrigin()->getPort());
        $this->assertEquals(base64_encode('admin@laravel.test'), $authenticationContext->getUserHandle()->toString());
        $this->assertEqualsCanonicalizing([
            CredentialId::fromString(base64_encode('id')),
        ], $authenticationContext->getAllowCredentialIds());
    }

    public function testGetClientOptionsAndVerificationRequired()
    {
        Config::shouldReceive('get')->with('filament-webauthn.auth.relying_party.name')->andReturn(1);
        Config::shouldReceive('get')->with('filament-webauthn.auth.relying_party.id')->andReturn(1);
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.user_verification')->andReturn('required');
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.timeout')->andReturn(60);
        $this->configureSession();

        $clientOptions = $this->createAuthenticator('admin@laravel.test')->getClientOptions();
        $clientOptions = json_decode($clientOptions, true);

        $this->assertEquals(86, strlen($clientOptions['challenge']));
        $this->assertEquals(60, $clientOptions['timeout']);
        $this->assertEquals('laravel.test', $clientOptions['rpId']);
        $this->assertIsArray($clientOptions['allowCredentials']);
        $this->assertEqualsCanonicalizing([
            'type' => 'public-key',
            'id' => 'aWQ',
        ], $clientOptions['allowCredentials'][0]);

        $authenticationContext = $this->getAuthenticationContextFromSession();
        $this->assertEquals('laravel.test', $authenticationContext->getRpId());
        $this->assertTrue($authenticationContext->isUserVerificationRequired());
        $this->assertTrue($authenticationContext->isUserPresenceRequired());
        $this->assertEquals('https', $authenticationContext->getOrigin()->getScheme());
        $this->assertEquals('laravel.test', $authenticationContext->getOrigin()->getHost());
        $this->assertEquals(443, $authenticationContext->getOrigin()->getPort());
        $this->assertEquals(base64_encode('admin@laravel.test'), $authenticationContext->getUserHandle()->toString());
        $this->assertEqualsCanonicalizing([
            CredentialId::fromString(base64_encode('id')),
        ], $authenticationContext->getAllowCredentialIds());
    }

    public function testGetClientOptionsAndVerificationPreferred()
    {
        Config::shouldReceive('get')->with('filament-webauthn.auth.relying_party.name')->andReturn(1);
        Config::shouldReceive('get')->with('filament-webauthn.auth.relying_party.id')->andReturn(1);
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.user_verification')->andReturn('preferred');
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.timeout')->andReturn(60);
        $this->configureSession();

        $clientOptions = $this->createAuthenticator('admin@laravel.test')->getClientOptions();
        $clientOptions = json_decode($clientOptions, true);

        $this->assertEquals(86, strlen($clientOptions['challenge']));
        $this->assertEquals(60, $clientOptions['timeout']);
        $this->assertEquals('laravel.test', $clientOptions['rpId']);
        $this->assertIsArray($clientOptions['allowCredentials']);
        $this->assertEqualsCanonicalizing([
            'type' => 'public-key',
            'id' => 'aWQ',
        ], $clientOptions['allowCredentials'][0]);

        $authenticationContext = $this->getAuthenticationContextFromSession();
        $this->assertEquals('laravel.test', $authenticationContext->getRpId());
        $this->assertFalse($authenticationContext->isUserVerificationRequired());
        $this->assertTrue($authenticationContext->isUserPresenceRequired());
        $this->assertEquals('https', $authenticationContext->getOrigin()->getScheme());
        $this->assertEquals('laravel.test', $authenticationContext->getOrigin()->getHost());
        $this->assertEquals(443, $authenticationContext->getOrigin()->getPort());
        $this->assertEquals(base64_encode('admin@laravel.test'), $authenticationContext->getUserHandle()->toString());
        $this->assertEqualsCanonicalizing([
            CredentialId::fromString(base64_encode('id')),
        ], $authenticationContext->getAllowCredentialIds());
    }

    public function testGetClientOptionsAndVerificationDiscouragedAndTimeoutIs120()
    {
        Config::shouldReceive('get')->with('filament-webauthn.auth.relying_party.name')->andReturn(1);
        Config::shouldReceive('get')->with('filament-webauthn.auth.relying_party.id')->andReturn(1);
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.user_verification')->andReturn('discouraged');
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.timeout')->andReturn(120);
        $this->configureSession();

        $clientOptions = $this->createAuthenticator('admin@laravel.test')->getClientOptions();
        $clientOptions = json_decode($clientOptions, true);

        $this->assertEquals(86, strlen($clientOptions['challenge']));
        $this->assertEquals(120, $clientOptions['timeout']);
        $this->assertEquals('laravel.test', $clientOptions['rpId']);
        $this->assertIsArray($clientOptions['allowCredentials']);
        $this->assertEqualsCanonicalizing([
            'type' => 'public-key',
            'id' => 'aWQ',
        ], $clientOptions['allowCredentials'][0]);

        $authenticationContext = $this->getAuthenticationContextFromSession();
        $this->assertEquals('laravel.test', $authenticationContext->getRpId());
        $this->assertFalse($authenticationContext->isUserVerificationRequired());
        $this->assertTrue($authenticationContext->isUserPresenceRequired());
        $this->assertEquals('https', $authenticationContext->getOrigin()->getScheme());
        $this->assertEquals('laravel.test', $authenticationContext->getOrigin()->getHost());
        $this->assertEquals(443, $authenticationContext->getOrigin()->getPort());
        $this->assertEquals(base64_encode('admin@laravel.test'), $authenticationContext->getUserHandle()->toString());
        $this->assertEqualsCanonicalizing([
            CredentialId::fromString(base64_encode('id')),
        ], $authenticationContext->getAllowCredentialIds());
    }

    private function createAuthenticator(string $loginId): AuthenticatorInterface
    {
        $userRepositoryMock = $this->mock(UserRepositoryInterface::class);
        $userRepositoryMock->shouldReceive('getUserIdByCredentialId')->andReturn(1);

        return new Authenticator(
            $this->createServer(),
            AuthenticationOptions::createForUser(
                UserHandle::fromString(
                    base64_encode($loginId)
                )
            ),
            $userRepositoryMock
        );
    }

    private function createServer(): ServerInterface
    {
        $relyingParty = new RelyingParty(
            'laravel',
            'https://laravel.test'
        );
        $relyingParty->setId('laravel.test');

        return (new ServerBuilder())
            ->setRelyingParty($relyingParty)
            ->setCredentialStore($this->createPublicKeyRepositoryMock())
            ->build();
    }

    private function createPublicKeyRepositoryMock(): MockInterface
    {
        $mock = $this->mock(CredentialStoreInterface::class);
        $mock->shouldReceive('findCredential')
            ->zeroOrMoreTimes()
            ->andReturn(
                new UserCredential(
                    CredentialId::fromString(base64_encode('id')),
                    unserialize(base64_decode('TzozMjoiTWFkV2l6YXJkXFdlYkF1dGhuXENyeXB0b1xFYzJLZXkiOjQ6e3M6NDQ6IgBNYWRXaXphcmRcV2ViQXV0aG5cQ3J5cHRvXENvc2VLZXkAYWxnb3JpdGhtIjtpOi03O3M6MzU6IgBNYWRXaXphcmRcV2ViQXV0aG5cQ3J5cHRvXEVjMktleQB4IjtPOjM2OiJNYWRXaXphcmRcV2ViQXV0aG5cRm9ybWF0XEJ5dGVCdWZmZXIiOjE6e3M6MToiZCI7czozMjoinIrdgExOVDOSYZU1hgxjFvr8J+wRCvVJvsW7hu1cgqMiO31zOjM1OiIATWFkV2l6YXJkXFdlYkF1dGhuXENyeXB0b1xFYzJLZXkAeSI7TzozNjoiTWFkV2l6YXJkXFdlYkF1dGhuXEZvcm1hdFxCeXRlQnVmZmVyIjoxOntzOjE6ImQiO3M6MzI6ImG9hopB5Wsd6Bu3Trhl0lKeJ7Nh26mY3ybQg6leUFBoIjt9czozOToiAE1hZFdpemFyZFxXZWJBdXRoblxDcnlwdG9cRWMyS2V5AGN1cnZlIjtpOjE7fQ==')),
                    UserHandle::fromString(base64_encode('admin@laravel.test'))
                )
            );
        $mock->shouldReceive('getUserCredentialIds')->zeroOrMoreTimes()->andReturn(
            [
                CredentialId::fromString(base64_encode('id')),
            ]
        );

        return $mock;
    }

    private function configureSession(): void
    {
        Config::shouldReceive('get')->with('session.driver')->andReturn('array');
        Config::shouldReceive('get')->with('session.lifetime')->andReturn(120);
        Config::shouldReceive('get')->with('session.encrypt')->andReturn(false);
        Config::shouldReceive('get')->with('session.cookie')->andReturn('cookie');
        Config::shouldReceive('get')->with('session.serialization', 'php')->andReturn('php');
    }

    private function getAuthenticationContextFromSession(): AuthenticationContext
    {
        return Session::get('filament:webauthn:login:'.base64_encode('admin@laravel.test'));
    }
}
