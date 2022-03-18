<?php

namespace Markhor\HubspotIntegration\v3API;

use HubSpot\Client\Crm\Properties\ApiException;
use HubSpot\Client\Crm\Properties\Model\BatchInputPropertyCreate;
use HubSpot\Client\Crm\Properties\Model\Error;
use HubSpot\Client\Crm\Properties\Model\PropertyCreate;

class Property extends InitService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function createProperty($objectType, $property)
    {
        try {
            $model = new PropertyCreate($property);
            $response = $this->hubSpot->crm()->properties()->coreApi()->create($objectType, $model);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process create property request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return $responseArray;
            }
            $this->logger->info(
                'Create property request completed.',
                [__METHOD__, $responseArray, func_get_args()]
            );

            return $responseArray;
        } catch (ApiException $th) {
            $this->logger->error(
                $th->getMessage(),
                [__METHOD__, json_decode($th->getResponseBody(), true)]
            );
            report($th);
            throwOrNotException($th);

            return json_decode($th->getResponseBody(), true);
        } catch (\Throwable $th) {
            $this->logger->error(
                $th->getMessage(),
                [__METHOD__, $th->getTrace()]
            );
            report($th);
            throwOrNotException($th);

            return false;
        }
    }

    public function updateProperty($objectType, $propertyName, $property)
    {
        try {
            $model = new PropertyCreate($property);
            $response = $this->hubSpot->crm()->properties()->coreApi()->update($objectType, $propertyName, $model);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process update property request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return $responseArray;
            }
            $this->logger->info(
                'Update property request completed.',
                [__METHOD__, $responseArray, func_get_args()]
            );

            return $responseArray;
        } catch (ApiException $th) {
            $this->logger->error(
                $th->getMessage(),
                [__METHOD__, json_decode($th->getResponseBody(), true)]
            );
            report($th);
            throwOrNotException($th);

            return json_decode($th->getResponseBody(), true);
        } catch (\Throwable $th) {
            $this->logger->error(
                $th->getMessage(),
                [__METHOD__, $th->getTrace()]
            );
            report($th);
            throwOrNotException($th);

            return false;
        }
    }

    public function createProperties($objectType, $properties)
    {
        try {
            $properties = array_map(function ($property) {
                return new PropertyCreate($property);
            }, $properties);
            $model = new BatchInputPropertyCreate(['inputs' => $properties]);
            $response = $this->hubSpot->crm()->properties()->batchApi()->create($objectType, $model);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process create properties request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return $responseArray;
            }
            $this->logger->info(
                'Create properties request completed.',
                [__METHOD__, $responseArray, func_get_args()]
            );

            return $responseArray;
        } catch (ApiException $th) {
            $this->logger->error(
                $th->getMessage(),
                [__METHOD__, json_decode($th->getResponseBody(), true)]
            );
            report($th);
            throwOrNotException($th);

            return json_decode($th->getResponseBody(), true);
        } catch (\Throwable $th) {
            $this->logger->error(
                $th->getMessage(),
                [__METHOD__, $th->getTrace()]
            );
            report($th);
            throwOrNotException($th);

            return false;
        }
    }

    public function readAll($objectType, $archived = false)
    {
        try {
            $response = $this->hubSpot->crm()->properties()->coreApi()->getAll($objectType, $archived);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process read all request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return $responseArray;
            }
            $this->logger->info(
                'Read all request completed.',
                [__METHOD__, $responseArray, func_get_args()]
            );

            return $responseArray;
        } catch (ApiException $th) {
            $this->logger->error(
                $th->getMessage(),
                [__METHOD__, json_decode($th->getResponseBody(), true)]
            );
            report($th);
            throwOrNotException($th);

            return json_decode($th->getResponseBody(), true);
        } catch (\Throwable $th) {
            $this->logger->error(
                $th->getMessage(),
                [__METHOD__, $th->getTrace()]
            );
            report($th);
            throwOrNotException($th);

            return false;
        }
    }
}
