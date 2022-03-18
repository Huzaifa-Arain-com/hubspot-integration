<?php

namespace Markhor\HubspotIntegration\v3API;

use HubSpot\Client\Crm\LineItems\ApiException;
use HubSpot\Client\Crm\LineItems\Model\BatchInputSimplePublicObjectBatchInput;
use HubSpot\Client\Crm\LineItems\Model\BatchInputSimplePublicObjectInput;
use HubSpot\Client\Crm\LineItems\Model\BatchResponseSimplePublicObjectWithErrors;
use HubSpot\Client\Crm\LineItems\Model\Error;
use HubSpot\Client\Crm\LineItems\Model\Filter;
use HubSpot\Client\Crm\LineItems\Model\FilterGroup;
use HubSpot\Client\Crm\LineItems\Model\PublicObjectSearchRequest;
use HubSpot\Client\Crm\LineItems\Model\SimplePublicObjectBatchInput;
use HubSpot\Client\Crm\LineItems\Model\SimplePublicObjectInput;

class LineItem extends InitService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function createLineItems($lineItems)
    {
        try {
            $batchInputSimplePublicObjectInputData = ['inputs' => []];
            foreach ($lineItems as $lineItem) {
                unset($lineItem['properties']['hs_object_id']);
                array_push(
                    $batchInputSimplePublicObjectInputData['inputs'],
                    new SimplePublicObjectInput(['properties' => $lineItem])
                );
            }
            $model = new BatchInputSimplePublicObjectInput($batchInputSimplePublicObjectInputData);

            $response = $this->hubSpot->crm()->lineItems()->batchApi()->create($model);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof BatchResponseSimplePublicObjectWithErrors || $response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process create line items request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return false;
            }
            $this->logger->info(
                'Create line items request completed.',
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

    public function updateLineItems($dealsProperties)
    {
        try {
            $batchInputSimplePublicObjectInputData = ['inputs' => []];
            foreach ($dealsProperties as $dealProperties) {
                $id = $dealProperties['hs_object_id'];
                unset($dealProperties['hs_object_id']);
                array_push(
                    $batchInputSimplePublicObjectInputData['inputs'],
                    new SimplePublicObjectBatchInput(['id' => $id, 'properties' => $dealProperties])
                );
            }
            $model = new BatchInputSimplePublicObjectBatchInput($batchInputSimplePublicObjectInputData);

            $response = $this->hubSpot->crm()->lineItems()->batchApi()->update($model);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof BatchResponseSimplePublicObjectWithErrors || $response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process update line items request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return false;
            }
            $this->logger->info(
                'Update line items request completed.',
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
        $filtersWithGroups,
        array $properties = null,
        int $limit = null,
        int $after = null,
        array $sorts = null,
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
            $response = $this->hubSpot->crm()->lineItems()->searchApi()->doSearch($request);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process line item search request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return false;
            }
            $this->logger->info(
                'Line item search request completed.',
                [__METHOD__, $responseArray, func_get_args()]
            );

            return json_decode($response->__toString(), true);
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

    public function createLineItem($properties)
    {
        try {
            $model = new SimplePublicObjectInput(['properties' => $properties]);
            $response = $this->hubSpot->crm()->lineItems()->basicApi()->create($model);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process create line item request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return $responseArray;
            }
            $this->logger->info(
                'Create line item request completed.',
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

    public function updateLineItem($id, $properties)
    {
        try {
            unset($properties['hs_object_id']);
            $model = new SimplePublicObjectInput(['properties' => $properties]);
            $response = $this->hubSpot->crm()->lineItems()->basicApi()->update($id, $model);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process update line item request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return $responseArray;
            }
            $this->logger->info(
                'Update line item request completed.',
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
