<?php

namespace Markhor\HubspotIntegration\Database\Factories;

use App\Models\DealLineItem;
use App\Models\Product;
use Faker\Generator as Faker;

$factory->define(DealLineItem::class, function (Faker $faker) {
    return [
        'hs_object_id' => null,
        'product_id' => factory(Product::class),
        'quantity' => $faker->randomDigitNotZero,
    ];
});
