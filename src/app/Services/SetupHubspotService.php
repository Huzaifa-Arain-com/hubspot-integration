<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Markhor\HubspotIntegration\Facades\v3\V3Property;

class SetupHubspotService
{
    private $_logger = null;

    public function __construct()
    {
        $this->_logger = Log::channel(config('hubspot-integration.log_channel'));
    }

    public function setupCompany()
    {
        try {
            $existingProperties = V3Property::readAll('companies');
            $propertyNames = array_column($existingProperties['results'], 'name');
            $propertiesToCreate = [];
            if (! in_array('mid_id', $propertyNames)) {
                array_push($propertiesToCreate, [
                    "name" => "mid_id",
                    "label" => "Middleware ID",
                    "type" => "string",
                    "field_type" => "text",
                    "group_name" => "companyinformation",
                    "description" => "This property hold the unique identifier of the record saved in the middleware.",
                ]);
            }
            if (count($propertiesToCreate) > 0) {
                foreach (array_chunk($propertiesToCreate, 10) as $chunk) {
                    V3Property::createProperties('companies', $chunk);
                }
            }
        } catch (\Throwable $th) {
            $this->_logger->error($th->getMessage(), [__METHOD__, $th->getTrace()]);
            report($th);
        }
    }

    public function setupContact()
    {
        try {
            $existingProperties = V3Property::readAll('contacts');
            $propertyNames = array_column($existingProperties['results'], 'name');
            $propertiesToCreate = [];
            if (! in_array('mid_id', $propertyNames)) {
                array_push($propertiesToCreate, [
                    "name" => "mid_id",
                    "label" => "Middleware ID",
                    "type" => "string",
                    "field_type" => "text",
                    "group_name" => "contactinformation",
                    "description" => "This property hold the unique identifier of the record saved in the middleware.",
                ]);
            }
            if (count($propertiesToCreate) > 0) {
                foreach (array_chunk($propertiesToCreate, 10) as $chunk) {
                    V3Property::createProperties('contacts', $chunk);
                }
            }
        } catch (\Throwable $th) {
            $this->_logger->error($th->getMessage(), [__METHOD__, $th->getTrace()]);
            report($th);
        }
    }

    public function setupDeal()
    {
        try {
            $existingProperties = V3Property::readAll('deals');
            $propertyNames = array_column($existingProperties['results'], 'name');
            $propertiesToCreate = [];
            if (! in_array('mid_id', $propertyNames)) {
                array_push($propertiesToCreate, [
                    "name" => "mid_id",
                    "label" => "Middleware ID",
                    "type" => "string",
                    "field_type" => "text",
                    "group_name" => "dealinformation",
                    "description" => "This property hold the unique identifier of the record saved in the middleware.",
                ]);
            }
            if (count($propertiesToCreate) > 0) {
                foreach (array_chunk($propertiesToCreate, 10) as $chunk) {
                    V3Property::createProperties('deals', $chunk);
                }
            }
        } catch (\Throwable $th) {
            $this->_logger->error($th->getMessage(), [__METHOD__, $th->getTrace()]);
            report($th);
        }
    }

    public function setupProduct()
    {
        try {
            $existingProperties = V3Property::readAll('products');
            $propertyNames = array_column($existingProperties['results'], 'name');
            $propertiesToCreate = [];
            if (! in_array('mid_id', $propertyNames)) {
                array_push($propertiesToCreate, [
                    "name" => "mid_id",
                    "label" => "Middleware ID",
                    "type" => "string",
                    "field_type" => "text",
                    "group_name" => "productinformation",
                    "description" => "This property hold the unique identifier of the record saved in the middleware.",
                ]);
            }
            if (count($propertiesToCreate) > 0) {
                foreach (array_chunk($propertiesToCreate, 10) as $chunk) {
                    V3Property::createProperties('products', $chunk);
                }
            }
        } catch (\Throwable $th) {
            $this->_logger->error($th->getMessage(), [__METHOD__, $th->getTrace()]);
            report($th);
        }
    }

    public function setupLineItem()
    {
        try {
            $existingProperties = V3Property::readAll('line_items');
            $propertyNames = array_column($existingProperties['results'], 'name');
            $propertiesToCreate = [];
            if (! in_array('mid_id', $propertyNames)) {
                array_push($propertiesToCreate, [
                    "name" => "mid_id",
                    "label" => "Middleware ID",
                    "type" => "string",
                    "field_type" => "text",
                    "group_name" => "lineiteminformation",
                    "description" => "This property hold the unique identifier of the record saved in the middleware.",
                ]);
            }
            if (count($propertiesToCreate) > 0) {
                foreach (array_chunk($propertiesToCreate, 10) as $chunk) {
                    V3Property::createProperties('line_items', $chunk);
                }
            }
        } catch (\Throwable $th) {
            $this->_logger->error($th->getMessage(), [__METHOD__, $th->getTrace()]);
            report($th);
        }
    }
}
