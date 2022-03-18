<?php

namespace Markhor\HubspotIntegration\Database\Seeds;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        factory(Product::class, 10)->create();
    }
}
