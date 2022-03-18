<?php

namespace Markhor\HubspotIntegration\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Markhor\HubspotIntegration\HubspotIntegration
 */
class HubspotIntegration extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'hubspot-integration';
    }
}
