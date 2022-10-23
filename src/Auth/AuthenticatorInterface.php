<?php

namespace Moontechs\FilamentWebauthn\Auth;

interface AuthenticatorInterface
{
    public function getClientOptions(): string;

    public function validateAndLogin(string $data, bool $remember = false): bool;
}
