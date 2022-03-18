<?php

namespace Markhor\HubspotIntegration\Database\Factories;

use App\Models\Deal;
use Faker\Generator as Faker;

$factory->define(Deal::class, function (Faker $faker) {
    return [
        'hs_object_id' => null,
        'deal_name' => $faker->text(25),
        'pipeline' => 'default',
        'amount' => $faker->randomDigitNotNull,
    ];
});
