<?php

namespace Integrations\HubspotIntegration\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Integrations\HubspotIntegration\HubspotIntegration
 */
class HubspotIntegration extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'hubspot-integration';
    }
}
