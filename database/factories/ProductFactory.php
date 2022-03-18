<?php

namespace Markhor\HubspotIntegration\Database\Factories;

use App\Models\Product;
use Faker\Generator as Faker;

$factory->define(Product::class, function (Faker $faker) {
    return [
        'hs_object_id' => null,
        'name' => $faker->text(10),
        'description' => $faker->text(20),
        'price' => $faker->randomFloat('2'),
    ];
});
