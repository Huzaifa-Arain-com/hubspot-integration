<?php

namespace Markhor\HubspotIntegration\Facades\v3;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array|bool createDeals($properties)
 * @method static array|bool updateDeals($properties)
 * @method static array|bool createDeal($properties)
 * @method static array|bool updateDeal($id,$properties)
 * @method static array|bool search($filtersWithGroups,array $properties = null,int $limit = null,int $after = null,array $sorts = null,$query = null)
 */

class V3Deal extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'V3Deal';
    }
}
