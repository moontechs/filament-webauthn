<?php

namespace Moontechs\FilamentWebauthn\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Moontechs\FilamentWebauthn\FilamentWebauthnServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Moontechs\\FilamentWebauthn\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            FilamentWebauthnServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_filament-webauthn_table.php.stub';
        $migration->up();
        */
    }
}
