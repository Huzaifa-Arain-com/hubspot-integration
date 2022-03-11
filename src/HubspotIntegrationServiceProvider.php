<?php

namespace Integrations\HubspotIntegration;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Integrations\HubspotIntegration\Commands\HubspotIntegrationCommand;

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
            ->hasConfigFile()
            ->hasViews();
    }
}
