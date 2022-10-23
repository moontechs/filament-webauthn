<x-filament::button
    class="w-full"
    color="primary"
    :icon="$icon"
    wire:click="redirectToLoginPage"
>
    {{ __('filament-webauthn.login-button-text') }}
</x-filament::button>
