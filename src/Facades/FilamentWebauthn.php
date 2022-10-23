<?php

namespace Moontechs\FilamentWebauthn\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Moontechs\FilamentWebauthn\FilamentWebauthn
 */
class FilamentWebauthn extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Moontechs\FilamentWebauthn\FilamentWebauthn::class;
    }
}
