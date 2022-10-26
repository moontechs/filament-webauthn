<?php

namespace Moontechs\FilamentWebauthn\Auth;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

class User implements UserInterface
{
    public function getUserLoginIdentificator(): ?string
    {
        /**
         * @var Model $model
         */
        $user = Filament::auth()->user();

        return $user->getAttributeValue(config('filament-webauthn.user.auth_identifier'));
    }
}
