<?php

function throwOrNotException($th)
{
    if (config('hubspot-integration.throw_enable')) {
        throw $th;
    }
}
