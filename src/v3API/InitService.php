<?php

namespace Markhor\HubspotIntegration\v3API;

use HubSpot\Discovery\Discovery;
use Illuminate\Support\Facades\Log;

class InitService
{
    protected Discovery $hubSpot;

    protected $logger;

    protected function __construct()
    {
        $this->logger = Log::channel(config('hubspot-integration.log_channel'));

        try {
            $this->hubSpot = app('v3HubSpotAPI');
        } catch (\Throwable $th) {
            $this->logger->error(
                $th->getMessage(),
                [__METHOD__, $th->getTrace()]
            );
            report($th);
            throwOrNotException($th);

            return false;
        }
    }
}
