<?php

namespace Markhor\HubspotIntegration\Facades\v3;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array|bool createCompanies($properties)
 * @method static array|bool updateCompanies($properties)
 * @method static array|bool createCompany($properties)
 * @method static array|bool updateCompany($id,$properties)
 * @method static array|bool search($filtersWithGroups,array $properties = null,int $limit = null,int $after = null,array $sorts = null,$query = null)
 */

class V3Company extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'V3Company';
    }
}
