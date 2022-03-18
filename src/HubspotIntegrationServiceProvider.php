<?php

namespace Markhor\HubspotIntegration;

use Markhor\HubspotIntegration\Commands\Routes;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class HubspotIntegrationServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('hubspot-integration')
            ->hasCommands([
                Routes::class,
            ])
            ->hasConfigFile('hubspot-integration')
            ->hasMigrations(
                'create_companies_table',
                'create_contacts_table',
                'create_products_table',
                'create_deals_table',
                'create_deal_line_items_table',
                'create_deal_associations_table'
            )
            ->hasRoutes('web')
            ->hasViews()
            ->hasAssets();
        $this
            ->hasServices()
            ->hasModels()
            ->hasDataTables()
            ->hasPublishableCommands()
            ->hasCode();
    }

    public function registeringPackage()
    {
        BindV3API::bindV3HubspotAPI();
    }

    public function packageBooted()
    {
        $this->app->make('Illuminate\Database\Eloquent\Factory')
            ->load(__DIR__ . '/../database/factories');
        BindV3API::bindFacades();
    }

    public function hasServices()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->package->basePath("/../src/app/Services") => app_path('Services'),
            ], "{$this->package->shortName()}-services");
        }

        return $this;
    }

    public function hasModels()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->package->basePath("/../src/app/Models") => app_path('Models'),
            ], "{$this->package->shortName()}-models");
        }

        return $this;
    }

    public function hasDataTables()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->package->basePath("/../src/app/DataTables") => app_path('DataTables'),
            ], "{$this->package->shortName()}-datatables");
        }

        return $this;
    }

    public function hasPublishableCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->package->basePath("/../src/app/Commands") => app_path('Console/Commands'),
            ], "{$this->package->shortName()}-cmds");
        }

        return $this;
    }

    public function hasCode()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    $this->package->basePath("/../src/app") => app_path(),

                ],
                "{$this->package->shortName()}-code"
            );
        }

        return $this;
    }
}
