<?php

namespace Moontechs\FilamentWebauthn\Tests\Unit\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use MadWizard\WebAuthn\Builder\ServerBuilder;
use MadWizard\WebAuthn\Config\RelyingParty;
use MadWizard\WebAuthn\Credential\CredentialId;
use MadWizard\WebAuthn\Credential\CredentialStoreInterface;
use MadWizard\WebAuthn\Credential\UserCredential;
use MadWizard\WebAuthn\Credential\UserHandle;
use MadWizard\WebAuthn\Server\Registration\RegistrationContext;
use MadWizard\WebAuthn\Server\Registration\RegistrationOptions;
use MadWizard\WebAuthn\Server\ServerInterface;
use MadWizard\WebAuthn\Server\UserIdentity;
use Mockery\MockInterface;
use Moontechs\FilamentWebauthn\Auth\Registrator;
use Moontechs\FilamentWebauthn\Auth\RegistratorInterface;
use Moontechs\FilamentWebauthn\Tests\TestCase;

class RegistratorTest extends TestCase
{
    public function testGetClientOptions()
    {
        Config::shouldReceive('get')->with('filament.auth.guard', null)->andReturn(null);
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.attestation')->andReturn('');
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.user_verification')->andReturn('');
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.timeout')->andReturn(60);
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.platform')->andReturn('');
        $this->configureSession();

        $clientOptions = $this->createRegistrator()->getClientOptions();
        $clientOptions = json_decode($clientOptions, true);

        $this->assertEquals(86, strlen($clientOptions['challenge']));
        $this->assertEquals(60, $clientOptions['timeout']);
        $this->assertEquals('laravel', $clientOptions['rp']['name']);
        $this->assertEquals('laravel.test', $clientOptions['rp']['id']);
        $this->assertIsArray($clientOptions['excludeCredentials']);
        $this->assertEqualsCanonicalizing([
            'type' => 'public-key',
            'id' => 'aWQ',
        ], $clientOptions['excludeCredentials'][0]);
        $this->assertEqualsCanonicalizing([
            'name' => 'admin@laravel.test',
            'id' => 'YWRtaW5AbGFyYXZlbC50ZXN0',
            'displayName' => 'admin@laravel.test',
        ], $clientOptions['user']);

        $registrationContext = $this->getRegistrationContextFromSession();

        $this->assertEquals('laravel.test', $registrationContext->getRpId());
        $this->assertFalse($registrationContext->isUserVerificationRequired());
        $this->assertTrue($registrationContext->isUserPresenceRequired());
        $this->assertEquals('https', $registrationContext->getOrigin()->getScheme());
        $this->assertEquals('laravel.test', $registrationContext->getOrigin()->getHost());
        $this->assertEquals(443, $registrationContext->getOrigin()->getPort());
        $this->assertEquals(base64_encode('admin@laravel.test'), $registrationContext->getUserHandle()->toString());
    }

    public function testGetClientOptionsAndVerificationRequired()
    {
        Config::shouldReceive('get')->with('filament.auth.guard', null)->andReturn(null);
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.attestation')->andReturn('');
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.user_verification')->andReturn('required');
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.timeout')->andReturn(60);
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.platform')->andReturn('');
        $this->configureSession();

        $clientOptions = $this->createRegistrator()->getClientOptions();
        $clientOptions = json_decode($clientOptions, true);

        $this->assertEquals(86, strlen($clientOptions['challenge']));
        $this->assertEquals(60, $clientOptions['timeout']);
        $this->assertEquals('laravel', $clientOptions['rp']['name']);
        $this->assertEquals('laravel.test', $clientOptions['rp']['id']);
        $this->assertIsArray($clientOptions['excludeCredentials']);
        $this->assertEqualsCanonicalizing([
            'type' => 'public-key',
            'id' => 'aWQ',
        ], $clientOptions['excludeCredentials'][0]);
        $this->assertEqualsCanonicalizing([
            'name' => 'admin@laravel.test',
            'id' => 'YWRtaW5AbGFyYXZlbC50ZXN0',
            'displayName' => 'admin@laravel.test',
        ], $clientOptions['user']);

        $registrationContext = $this->getRegistrationContextFromSession();

        $this->assertEquals('laravel.test', $registrationContext->getRpId());
        $this->assertTrue($registrationContext->isUserVerificationRequired());
        $this->assertTrue($registrationContext->isUserPresenceRequired());
        $this->assertEquals('https', $registrationContext->getOrigin()->getScheme());
        $this->assertEquals('laravel.test', $registrationContext->getOrigin()->getHost());
        $this->assertEquals(443, $registrationContext->getOrigin()->getPort());
        $this->assertEquals(base64_encode('admin@laravel.test'), $registrationContext->getUserHandle()->toString());
    }

    public function testGetClientOptionsAndVerificationPreferred()
    {
        Config::shouldReceive('get')->with('filament.auth.guard', null)->andReturn(null);
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.attestation')->andReturn('');
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.user_verification')->andReturn('preferred');
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.timeout')->andReturn(60);
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.platform')->andReturn('');
        $this->configureSession();

        $clientOptions = $this->createRegistrator()->getClientOptions();
        $clientOptions = json_decode($clientOptions, true);

        $this->assertEquals(86, strlen($clientOptions['challenge']));
        $this->assertEquals(60, $clientOptions['timeout']);
        $this->assertEquals('laravel', $clientOptions['rp']['name']);
        $this->assertEquals('laravel.test', $clientOptions['rp']['id']);
        $this->assertIsArray($clientOptions['excludeCredentials']);
        $this->assertEqualsCanonicalizing([
            'type' => 'public-key',
            'id' => 'aWQ',
        ], $clientOptions['excludeCredentials'][0]);
        $this->assertEqualsCanonicalizing([
            'name' => 'admin@laravel.test',
            'id' => 'YWRtaW5AbGFyYXZlbC50ZXN0',
            'displayName' => 'admin@laravel.test',
        ], $clientOptions['user']);

        $registrationContext = $this->getRegistrationContextFromSession();

        $this->assertEquals('laravel.test', $registrationContext->getRpId());
        $this->assertFalse($registrationContext->isUserVerificationRequired());
        $this->assertTrue($registrationContext->isUserPresenceRequired());
        $this->assertEquals('https', $registrationContext->getOrigin()->getScheme());
        $this->assertEquals('laravel.test', $registrationContext->getOrigin()->getHost());
        $this->assertEquals(443, $registrationContext->getOrigin()->getPort());
        $this->assertEquals(base64_encode('admin@laravel.test'), $registrationContext->getUserHandle()->toString());
    }

    public function testGetClientOptionsAndVerificationDiscouragedAndTimeoutIs120()
    {
        Config::shouldReceive('get')->with('filament.auth.guard', null)->andReturn(null);
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.attestation')->andReturn('');
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.user_verification')->andReturn('discouraged');
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.timeout')->andReturn(120);
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.platform')->andReturn('');
        $this->configureSession();

        $clientOptions = $this->createRegistrator()->getClientOptions();
        $clientOptions = json_decode($clientOptions, true);

        $this->assertEquals(86, strlen($clientOptions['challenge']));
        $this->assertEquals(120, $clientOptions['timeout']);
        $this->assertEquals('laravel', $clientOptions['rp']['name']);
        $this->assertEquals('laravel.test', $clientOptions['rp']['id']);
        $this->assertIsArray($clientOptions['excludeCredentials']);
        $this->assertEqualsCanonicalizing([
            'type' => 'public-key',
            'id' => 'aWQ',
        ], $clientOptions['excludeCredentials'][0]);
        $this->assertEqualsCanonicalizing([
            'name' => 'admin@laravel.test',
            'id' => 'YWRtaW5AbGFyYXZlbC50ZXN0',
            'displayName' => 'admin@laravel.test',
        ], $clientOptions['user']);

        $registrationContext = $this->getRegistrationContextFromSession();

        $this->assertEquals('laravel.test', $registrationContext->getRpId());
        $this->assertFalse($registrationContext->isUserVerificationRequired());
        $this->assertTrue($registrationContext->isUserPresenceRequired());
        $this->assertEquals('https', $registrationContext->getOrigin()->getScheme());
        $this->assertEquals('laravel.test', $registrationContext->getOrigin()->getHost());
        $this->assertEquals(443, $registrationContext->getOrigin()->getPort());
        $this->assertEquals(base64_encode('admin@laravel.test'), $registrationContext->getUserHandle()->toString());
    }

    public function testGetClientOptionsAndAttestationDirectAndTimeoutIs120()
    {
        Config::shouldReceive('get')->with('filament.auth.guard', null)->andReturn(null);
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.attestation')->andReturn('direct');
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.user_verification')->andReturn('');
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.timeout')->andReturn(120);
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.platform')->andReturn('');
        $this->configureSession();

        $clientOptions = $this->createRegistrator()->getClientOptions();
        $clientOptions = json_decode($clientOptions, true);

        $this->assertEquals(86, strlen($clientOptions['challenge']));
        $this->assertEquals(120, $clientOptions['timeout']);
        $this->assertEquals('laravel', $clientOptions['rp']['name']);
        $this->assertEquals('laravel.test', $clientOptions['rp']['id']);
        $this->assertIsArray($clientOptions['excludeCredentials']);
        $this->assertEqualsCanonicalizing([
            'type' => 'public-key',
            'id' => 'aWQ',
        ], $clientOptions['excludeCredentials'][0]);
        $this->assertEqualsCanonicalizing([
            'name' => 'admin@laravel.test',
            'id' => 'YWRtaW5AbGFyYXZlbC50ZXN0',
            'displayName' => 'admin@laravel.test',
        ], $clientOptions['user']);

        $registrationContext = $this->getRegistrationContextFromSession();

        $this->assertEquals('laravel.test', $registrationContext->getRpId());
        $this->assertFalse($registrationContext->isUserVerificationRequired());
        $this->assertTrue($registrationContext->isUserPresenceRequired());
        $this->assertEquals('https', $registrationContext->getOrigin()->getScheme());
        $this->assertEquals('laravel.test', $registrationContext->getOrigin()->getHost());
        $this->assertEquals(443, $registrationContext->getOrigin()->getPort());
        $this->assertEquals(base64_encode('admin@laravel.test'), $registrationContext->getUserHandle()->toString());
    }

    public function testGetClientOptionsAndAllConfigValuesAreNull()
    {
        Config::shouldReceive('get')->with('filament.auth.guard', null)->andReturn(null);
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.attestation')->andReturn(null);
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.user_verification')->andReturn(null);
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.timeout')->andReturn(null);
        Config::shouldReceive('get')->with('filament-webauthn.auth.client_options.platform')->andReturn(null);
        $this->configureSession();

        $clientOptions = $this->createRegistrator()->getClientOptions();
        $clientOptions = json_decode($clientOptions, true);

        $this->assertEquals(86, strlen($clientOptions['challenge']));
        $this->assertEquals('laravel', $clientOptions['rp']['name']);
        $this->assertEquals('laravel.test', $clientOptions['rp']['id']);
        $this->assertIsArray($clientOptions['excludeCredentials']);
        $this->assertEqualsCanonicalizing([
            'type' => 'public-key',
            'id' => 'aWQ',
        ], $clientOptions['excludeCredentials'][0]);
        $this->assertEqualsCanonicalizing([
            'name' => 'admin@laravel.test',
            'id' => 'YWRtaW5AbGFyYXZlbC50ZXN0',
            'displayName' => 'admin@laravel.test',
        ], $clientOptions['user']);

        $registrationContext = $this->getRegistrationContextFromSession();

        $this->assertEquals('laravel.test', $registrationContext->getRpId());
        $this->assertFalse($registrationContext->isUserVerificationRequired());
        $this->assertTrue($registrationContext->isUserPresenceRequired());
        $this->assertEquals('https', $registrationContext->getOrigin()->getScheme());
        $this->assertEquals('laravel.test', $registrationContext->getOrigin()->getHost());
        $this->assertEquals(443, $registrationContext->getOrigin()->getPort());
        $this->assertEquals(base64_encode('admin@laravel.test'), $registrationContext->getUserHandle()->toString());
    }

    private function createRegistrator(): RegistratorInterface
    {
        $userMock = $this->getMockBuilder(Authenticatable::class)
            ->getMock();
        $userMock->method('getAuthIdentifier')->willReturn('admin@laravel.test');

        return new Registrator(
            $this->createServer(),
            $this->createRegistrationOptions($this->createUserIdentity()),
            $userMock
        );
    }

    public function createUserIdentity(): UserIdentity
    {
        $userName = 'admin@laravel.test';

        return new UserIdentity(
            UserHandle::fromString(base64_encode($userName)),
            $userName,
            $userName
        );
    }

    private function createRegistrationOptions(UserIdentity $user): RegistrationOptions
    {
        $registrationOptions = RegistrationOptions::createForUser($user);
        $registrationOptions->setExcludeExistingCredentials(true);

        return $registrationOptions;
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

    private function getRegistrationContextFromSession(): RegistrationContext
    {
        return Session::get('filament:webauthn:register:admin@laravel.test');
    }
}
