<?php

namespace Moontechs\FilamentWebauthn\Auth;

interface RegistratorInterface
{
    public function getClientOptions(): string;

    public function validateAndRegister(string $data): bool;
}
