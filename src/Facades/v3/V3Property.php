<?php

namespace Markhor\HubspotIntegration\Facades\v3;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array|bool createProperties($objectType,$properties)
 * @method static array|bool createProperty($objectType,$property)
 * @method static array|bool updateProperty($objectType,$propertyName,$property)
 * @method static array|bool readAll($objectType,$archived = false)
 */

class V3Property extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'V3Property';
    }
}
