<?php

namespace Moontechs\FilamentWebauthn\Repositories;

use Illuminate\Support\Facades\DB;

class UserRepository implements UserRepositoryInterface
{
    public function getUserIdByCredentialId(string $credentialId): ?int
    {
        $userEntity = DB::table('users')
            ->where(config('filament-webauthn.user.auth_identifier'), '=', $credentialId)
            ->first(['id']);

        return $userEntity?->id;
    }
}
