<?php

namespace Markhor\HubspotIntegration\Database\Factories;

use App\Models\Company;
use Faker\Generator as Faker;

$factory->define(Company::class, function (Faker $faker) {
    return [
        'hs_object_id' => null,
        'name' => $faker->company,
    ];
});
