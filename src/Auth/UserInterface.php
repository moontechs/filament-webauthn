<?php

namespace Moontechs\FilamentWebauthn\Auth;

interface UserInterface
{
    public function getUserLoginIdentificator(): ?string;
}
