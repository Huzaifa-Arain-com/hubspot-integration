<?php

namespace Markhor\HubspotIntegration\v3API;

use HubSpot\Client\Crm\Objects\ApiException;
use HubSpot\Client\Crm\Objects\Model\BatchInputSimplePublicObjectBatchInput;
use HubSpot\Client\Crm\Objects\Model\BatchInputSimplePublicObjectInput;
use HubSpot\Client\Crm\Objects\Model\BatchResponseSimplePublicObjectWithErrors;
use HubSpot\Client\Crm\Objects\Model\Error;
use HubSpot\Client\Crm\Objects\Model\Filter;
use HubSpot\Client\Crm\Objects\Model\FilterGroup;
use HubSpot\Client\Crm\Objects\Model\PublicObjectSearchRequest;
use HubSpot\Client\Crm\Objects\Model\SimplePublicObjectBatchInput;
use HubSpot\Client\Crm\Objects\Model\SimplePublicObjectInput;

class CustomObject extends InitService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function createCustomObject($objectType, $batchProperties)
    {
        try {
            $batchInputSimplePublicObjectInputData = ['inputs' => []];
            foreach ($batchProperties as $properties) {
                array_push(
                    $batchInputSimplePublicObjectInputData['inputs'],
                    new SimplePublicObjectInput(['properties' => $properties])
                );
            }

            $batchInputSimplePublicObjectInput = new BatchInputSimplePublicObjectInput(
                $batchInputSimplePublicObjectInputData
            );
            $response = $this->hubSpot->crm()->objects()->batchApi()->create(
                $objectType,
                $batchInputSimplePublicObjectInput
            );
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof BatchResponseSimplePublicObjectWithErrors || $response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process create custom object request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return false;
            }

            $this->logger->info(
                'Create custom object request completed.',
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

    public function associateCustomObject($objectType, $objectId, $toObjectType, $toObjectId, $associationType)
    {
        try {
            $response = $this->hubSpot->crm()->objects()->AssociationsApi()->create(
                $objectType,
                $objectId,
                $toObjectType,
                $toObjectId,
                $associationType
            );
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process associate custom object request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return false;
            }
            $this->logger->info(
                'Associate custom object request completed.',
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

    public function getCustomObjects()
    {
        try {
            $response = $this->hubSpot->crm()->schemas()->CoreApi()->getAll();
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process get custom objects request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return false;
            }
            $this->logger->info(
                'Get custom objects request completed.',
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

    public function updateCustomObject($objectType, $objectsProperties)
    {
        try {
            $batchInputSimplePublicObjectInputData = ['inputs' => []];
            foreach ($objectsProperties as $objectProperties) {
                $id = $objectProperties['hs_object_id'];
                unset($objectProperties['hs_object_id']);
                array_push(
                    $batchInputSimplePublicObjectInputData['inputs'],
                    new SimplePublicObjectBatchInput(['id' => $id, 'properties' => $objectProperties])
                );
            }

            $batchObjects = new BatchInputSimplePublicObjectBatchInput($batchInputSimplePublicObjectInputData);
            $response = $this->hubSpot->crm()->objects()->batchApi()->update($objectType, $batchObjects);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof BatchResponseSimplePublicObjectWithErrors || $response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process update custom object request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return false;
            }
            $this->logger->info(
                'Update custom object request completed.',
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

    public function getById(
        $object_type,
        $object_id,
        $properties = null,
        $associations = null,
        $archived = false,
        $id_property = null
    ) {
        try {
            $response = $this->hubSpot->crm()->objects()->basicApi()->getById(
                $object_type,
                $object_id,
                $properties,
                $associations,
                $archived,
                $id_property
            );
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process get custom object by id request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return false;
            }
            $this->logger->info(
                'Get custom object by id request completed.',
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

    public function search(
        $object_type,
        $filtersWithGroups,
        $properties = null,
        $limit = null,
        $after = null,
        $sorts = null,
        $query = null
    ) {
        try {
            $filterGroups = [];
            foreach ($filtersWithGroups as $key => $group) {
                $filters = [];
                foreach ($group as $filter) {
                    array_push($filters, new Filter(
                        [
                            'value' => $filter['value'] ?? null,
                            'property_name' => $filter['property'] ?? null,
                            'operator' => $filter['operator'] ?? null,
                        ]
                    ));
                }
                array_push(
                    $filterGroups,
                    new FilterGroup(
                        [
                            'filters' => $filters,
                        ]
                    )
                );
            }
            $request = new PublicObjectSearchRequest([
                'filter_groups' => $filterGroups,
                'sorts' => $sorts,
                'query' => $query,
                'properties' => $properties,
                'limit' => $limit,
                'after' => $after,
            ]);
            $response = $this->hubSpot->crm()->objects()->searchApi()->doSearch($object_type, $request);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process search request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return false;
            }
            $this->logger->info(
                'Search request completed.',
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
