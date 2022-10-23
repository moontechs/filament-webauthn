<?php

namespace Moontechs\FilamentWebauthn\Repositories;

use MadWizard\WebAuthn\Credential\CredentialId;
use MadWizard\WebAuthn\Credential\CredentialStoreInterface;
use MadWizard\WebAuthn\Credential\UserCredential;
use MadWizard\WebAuthn\Credential\UserCredentialInterface;
use MadWizard\WebAuthn\Credential\UserHandle;
use MadWizard\WebAuthn\Crypto\CoseKeyInterface;
use Moontechs\FilamentWebauthn\Models\WebauthnKey;

class PublicKeyRepository implements CredentialStoreInterface
{
    public function findCredential(CredentialId $credentialId): ?UserCredentialInterface
    {
        /**
         * @var WebauthnKey|null $webauthnKey
         */
        $webauthnKey = WebauthnKey::where('credential_id', $credentialId->toString())->first();

        if ($webauthnKey === null) {
            return null;
        }

        /**
         * @var CoseKeyInterface $key
         */
        $key = unserialize(base64_decode($webauthnKey->public_key));

        return new UserCredential(
            $credentialId,
            $key,
            UserHandle::fromString($webauthnKey->user_handle)
        );
    }

    public function getSignatureCounter(CredentialId $credentialId): ?int
    {
        return null;
    }

    public function updateSignatureCounter(CredentialId $credentialId, int $counter): void
    {
    }

    public function getUserCredentialIds(UserHandle $userHandle): array
    {
        $webauthnKeys = WebauthnKey::where('user_handle', $userHandle->toString())
            ->get();

        if ($webauthnKeys->count() === 0) {
            return [];
        }

        return $webauthnKeys->pluck('credential_id')->map(function ($item, $key) {
            return CredentialId::fromString($item);
        })->all();
    }
}
