<?php

namespace Markhor\HubspotIntegration\Database\Seeds;

use App\Models\Company;
use App\Models\Contact;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // factory(Contact::class, 10)->create()->each(function ($contact) {
        //     $contact->company()->associate(factory(Company::class)->create())->save();
        // });
    }
}
