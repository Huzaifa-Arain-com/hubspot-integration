<?php

namespace Markhor\HubspotIntegration\Database\Factories;

use App\Models\Company;
use App\Models\DealAssociation;
use Faker\Generator as Faker;

$factory->define(DealAssociation::class, function (Faker $faker) {
    return [
        'associateable_type' => Company::class,
        'associateable_id' => factory(Company::class),
    ];
});
