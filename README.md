# Filament Webauthn Authentication (FIDO)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/moontechs/filament-webauthn.svg?style=flat-square)](https://packagist.org/packages/moontechs/filament-webauthn)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/moontechs/filament-webauthn/.github/workflows/run-tests.yml?branch=main)](https://github.com/moontechs/filament-webauthn/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/moontechs/filament-webauthn/.github/workflows/fix-php-code-style-issues.yml?branch=main)](https://github.com/moontechs/filament-webauthn/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/moontechs/filament-webauthn.svg?style=flat-square)](https://packagist.org/packages/moontechs/filament-webauthn)

Passwordless login for your Filament app. Web Authentication server-side and front-end components.

The package has the following components:
* registration button and widget
* login form extension to redirect to the webauthn login page
* separate route and page with webauthn login form

Should work with HTTPS and not localhost only.

## Installation

You can install the package via composer:

```bash
composer require moontechs/filament-webauthn
```

You should publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-webauthn-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-webauthn-config"
```

This is the contents of the published config file:

```php
return [
    'login_page_url' => '/webauthn-login',
    'user' => [
        'auth_identifier' => 'email', // column in users table with unique user id
    ],
    'widget' => [
        'column_span' => '',
    ],
    'register_button' => [
        'icon' => 'heroicon-o-key',
        'class' => 'w-full',
    ],
    'login_button' => [
        'icon' => 'heroicon-o-key',
        'class' => 'w-full',
    ],
    'auth' => [
        'relying_party' => [
            'name' => env('APP_NAME'),
            'origin' => env('APP_URL'),
            'id' => env('APP_HOST', parse_url(env('APP_URL'))['host']),
        ],
        'client_options' => [
            'timeout' => 60000,
            'platform' => '', // available: platform, cross-platform, or leave empty
            'attestation' => 'direct', // available: direct, indirect, none
            'user_verification' => 'required', // available: required, preferred, discouraged
        ],
    ],
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-webauthn-views"
```

You can publish the translation file with:

```bash
php artisan vendor:publish --tag="filament-webauthn-translations"
```

## Usage

* Install the package.
* Publish migrations and migrate.

### Registration widget
Only signed-in users can register a device to be able to sign in to use it in the future.

* Register `Moontechs\FilamentWebauthn\Widgets\WebauthnRegisterWidget::class` widget. 
Add it to the `widgets.register` array of the Filament config.

![widget](images/widget.png?raw=true)

#### Customization
* Publish the config file
* `widget.column_span` - widget width ([docs](https://filamentphp.com/docs/2.x/admin/dashboard/getting-started#customizing-widget-width))

### Registration button (without widget)
* Add `<livewire:webauthn-register-button/>` in any view.

#### Customization
* Publish the config file
* `register_button.icon` - choose any available icon
* `register_button.class` - add more classes or change the default one 

### Redirect to the login page button

* Publish Filament login page view `php artisan vendor:publish --tag=filament-views`
* Add `<x-filament-webauthn::login-form-extension />` in the end of the login form.

If you didn't want to use this button, you can use a simple redirect to a named route `filament-webauthn.login`.

![redirect to login page](images/reditect-to-login-page.png?raw=true)

### Login form
#### Customization
* Publish the config file
* `login_button.icon` - choose any available icon
* `login_button.class` - add more classes or change the default one

![login](images/login.png?raw=true)

## Testing

```bash
composer test
```

## Credits

- [Michael Kozii](https://github.com/mkoziy)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
