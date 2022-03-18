<?php

namespace Markhor\HubspotIntegration\Facades\v3;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array|bool createLineItems($properties)
 * @method static array|bool updateLineItems($properties)
 * @method static array|bool createLineItem($properties)
 * @method static array|bool updateLineItem($id,$properties)
 * @method static array|bool search($filtersWithGroups,array $properties = null,int $limit = null,int $after = null,array $sorts = null,$query = null)
 */

class V3LineItem extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'V3LineItem';
    }
}
