const mix = require("laravel-mix");

mix.disableNotifications();

mix.disableSuccessNotifications()

mix.options({
    terser: {
        extractComments: false,
    },
})

mix.setPublicPath("resources/assets/dist")
mix.version()

mix.js("./resources/assets/js/filament-webauthn.js", "./resources/assets/dist")

mix.options({
    processCssUrls: false,
});
