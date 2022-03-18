<?php

namespace Markhor\HubspotIntegration\Database\Seeds;

use App\Models\Contact;
use App\Models\Deal;
use App\Models\DealAssociation;
use App\Models\DealLineItem;
use Illuminate\Database\Seeder;

class DealSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        factory(Deal::class, 5)->create()->each(function ($deal) {
            $associations = factory(DealAssociation::class, rand(1, 2))->make(['deal_id' => $deal->id]);
            $associationsArray = $associations->toArray();
            $associations->each(function ($association) {
                $association->associateable->contacts()->createMany(
                    factory(Contact::class, rand(1, 2))->make()->toArray()
                );
            });
            $deal->associations()->createMany(
                $associationsArray
            );
            $deal->lineItems()->createMany(
                factory(DealLineItem::class, rand(1, 3))->make()->toArray()
            );
        });
    }
}
