<?php

namespace Markhor\HubspotIntegration\Facades\v3;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array|bool batchAssociate($fromObjectType, $toObjectType, array $entities)
 */

class V3Association extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'V3Association';
    }
}
