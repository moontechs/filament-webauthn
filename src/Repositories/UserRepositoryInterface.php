<?php

namespace Moontechs\FilamentWebauthn\Repositories;

interface UserRepositoryInterface
{
    public function getUserIdByCredentialId(string $credentialId): ?int;
}
