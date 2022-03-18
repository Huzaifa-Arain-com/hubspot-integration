<?php

namespace Markhor\HubspotIntegration;

use HubSpot\Factory;
use Markhor\HubspotIntegration\v3API\Association;
use Markhor\HubspotIntegration\v3API\Company;
use Markhor\HubspotIntegration\v3API\Contact;
use Markhor\HubspotIntegration\v3API\Deal;
use Markhor\HubspotIntegration\v3API\LineItem;
use Markhor\HubspotIntegration\v3API\Product;
use Markhor\HubspotIntegration\v3API\Property;

class BindV3API
{
    public static function bindV3HubspotAPI()
    {
        app()->singleton("v3HubSpotAPI", function ($app) {
            return Factory::createWithApiKey(config("hubspot-integration.api_key"));
        });
    }

    public static function bindFacades()
    {
        app()->bind('V3Company', function ($app) {
            return new Company();
        });
        app()->bind('V3Contact', function ($app) {
            return new Contact();
        });
        app()->bind('V3Deal', function ($app) {
            return new Deal();
        });
        app()->bind('V3Product', function ($app) {
            return new Product();
        });
        app()->bind('V3Association', function ($app) {
            return new Association();
        });
        app()->bind('V3LineItem', function ($app) {
            return new LineItem();
        });
        app()->bind('V3Property', function ($app) {
            return new Property();
        });
    }
}
