<div class="relative flex items-center justify-center text-center">
    <div class="absolute border-t border-gray-200 w-full h-px"></div>
    <p class="inline-block relative bg-white text-sm p-2 rounded-full font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-100">
        {{ __('filament-webauthn.or') }}
    </p>
</div>

<div class="grid">
    <livewire:webauthn-redirect-to-login-button/>
</div>
