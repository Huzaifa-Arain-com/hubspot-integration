<?php

namespace Markhor\HubspotIntegration\Facades\v3;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array|bool createContacts($properties)
 * @method static array|bool updateContacts($properties)
 * @method static array|bool createContact($properties)
 * @method static array|bool updateContact($id,$properties)
 * @method static array|bool search($filtersWithGroups,array $properties = null,int $limit = null,int $after = null,array $sorts = null,$query = null)
 */
class V3Contact extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'V3Contact';
    }
}
