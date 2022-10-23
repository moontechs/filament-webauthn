<x-filament::button
    :class="$class"
    color="primary"
    :icon="$icon"
    wire:click="register"
>
    {{ __('filament-webauthn.register-button-text') }}
</x-filament::button>

<script>
    document.addEventListener('livewire:load', function () {
        @this.on('register', async function (clientOptions) {
            if (!window.FilamentWebauthn.supported()) {
                @this.notifyUnsupported();
                return;
            }

            const options = window.FilamentWebauthn.parseCreationOptionsFromJSON({"publicKey": JSON.parse(clientOptions)});
            window.FilamentWebauthn.create(options)
                .then(response => @this.validateAndRegister(JSON.stringify(response)))
                .catch(err => @this.notifyError(err.message));
        });
    })
</script>
