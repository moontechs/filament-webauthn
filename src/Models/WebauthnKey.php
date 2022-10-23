<?php

namespace Moontechs\FilamentWebauthn\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $credential_id
 * @property string $public_key
 * @property string $user_handle
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class WebauthnKey extends Model
{
    protected $fillable = [
        'credential_id',
        'public_key',
        'user_handle',
        'user_id',
    ];
}
