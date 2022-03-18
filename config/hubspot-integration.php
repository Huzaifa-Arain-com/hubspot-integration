<?php

// config for Markhor/HubspotIntegration
return [
    'api_key' => env('MARKHOR_HUBSPOT_API_KEY', null),
    'log_channel' => env('MARKHOR_LOG_CHANNEL', config('logging.default')),
    'portal_id' => env('MARKHOR_HUBSPOT_PORTAL_ID'),
    'base_url' => env('MARKHOR_HUBSPOT_BASE_URL', 'https://app.hubspot.com/'),
    'throw_enable' => env('MARKHOR_THROW_ENABLE', config('app.debug')),
];
