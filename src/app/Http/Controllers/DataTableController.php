<?php

namespace App\Http\Controllers;

use App\DataTables\CompanyDataTable;
use App\DataTables\ContactDataTable;
use App\DataTables\DealDataTable;
use App\DataTables\ProductDataTable;

class DataTableController extends Controller
{
    public function companies(CompanyDataTable $companyDataTable)
    {
        return $companyDataTable->render('vendor.hubspot-integration.datatable.datatable');
    }

    public function contacts(ContactDataTable $contactDataTable)
    {
        return $contactDataTable->render('hubspot-integration::datatable');
    }

    public function deals(DealDataTable $dealDataTable)
    {
        return $dealDataTable->render('hubspot-integration::datatable');
    }

    public function products(ProductDataTable $productDataTable)
    {
        return $productDataTable->render('hubspot-integration::datatable');
    }
}
