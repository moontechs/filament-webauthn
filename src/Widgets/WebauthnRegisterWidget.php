<?php

namespace Moontechs\FilamentWebauthn\Widgets;

use Filament\Widgets\Widget;

class WebauthnRegisterWidget extends Widget
{
    protected static string $view = 'filament-webauthn::filament/widgets/webauthn-register';

    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->columnSpan = config('filament-webauthn.widget.column_span', '');
    }
}
