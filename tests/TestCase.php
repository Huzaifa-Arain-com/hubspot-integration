<?php

namespace Markhor\HubspotIntegration\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Integrations\HubspotIntegration\HubspotIntegrationServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Integrations\\HubspotIntegration\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            HubspotIntegrationServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_hubspot-integration_table.php.stub';
        $migration->up();
        */
    }
}
