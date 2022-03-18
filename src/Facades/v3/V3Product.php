<?php

namespace Markhor\HubspotIntegration\Facades\v3;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array|bool createProducts($properties)
 * @method static array|bool updateProducts($properties)
 * @method static array|bool createProduct($properties)
 * @method static array|bool updateProduct($id,$properties)
 * @method static array|bool search($filtersWithGroups,array $properties = null,int $limit = null,int $after = null,array $sorts = null,$query = null)
 */

class V3Product extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'V3Product';
    }
}
