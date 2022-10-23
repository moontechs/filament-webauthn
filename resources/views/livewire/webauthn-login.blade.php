<form wire:submit.prevent="" class="space-y-8">
    {{ $this->form }}

    <x-filament::button
        type="submit"
        class="w-full"
        color="primary"
        :icon="$icon"
        wire:click="getClientOptions"
    >
        {{ __('filament::login.buttons.submit.label') }}
    </x-filament::button>
</form>

<script>
    document.addEventListener('livewire:load', function () {
        @this.on('clientOptions', async function () {
            if (!window.FilamentWebauthn.supported()) {
                @this.notifyUnsupported();
                return;
            }

            if (@this.clientOptions === '') {
                return;
            }
            const options = window.FilamentWebauthn.parseRequestOptionsFromJSON({"publicKey": JSON.parse(@this.clientOptions)});
            await window.FilamentWebauthn.get(options)
                .then(response => @this.authenticate(JSON.stringify(response)))
                .catch(err => @this.notifyError(err.message));
        });
    })
</script>
