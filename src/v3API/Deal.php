<?php

namespace Markhor\HubspotIntegration\v3API;

use HubSpot\Client\Crm\Deals\ApiException;
use HubSpot\Client\Crm\Deals\Model\BatchInputSimplePublicObjectBatchInput;
use HubSpot\Client\Crm\Deals\Model\BatchInputSimplePublicObjectInput;
use HubSpot\Client\Crm\Deals\Model\BatchResponseSimplePublicObjectWithErrors;
use HubSpot\Client\Crm\Deals\Model\Error;
use HubSpot\Client\Crm\Deals\Model\Filter;
use HubSpot\Client\Crm\Deals\Model\FilterGroup;
use HubSpot\Client\Crm\Deals\Model\PublicObjectSearchRequest;
use HubSpot\Client\Crm\Deals\Model\SimplePublicObjectBatchInput;
use HubSpot\Client\Crm\Deals\Model\SimplePublicObjectInput;

class Deal extends InitService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function createDeals($dealsProperties)
    {
        try {
            $batchInputSimplePublicObjectInputData = ['inputs' => []];
            foreach ($dealsProperties as $dealProperties) {
                unset($dealProperties['properties']['hs_object_id']);
                array_push(
                    $batchInputSimplePublicObjectInputData['inputs'],
                    new SimplePublicObjectInput(['properties' => $dealProperties])
                );
            }
            $batchDeals = new BatchInputSimplePublicObjectInput($batchInputSimplePublicObjectInputData);

            $response = $this->hubSpot->crm()->deals()->batchApi()->create($batchDeals);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof BatchResponseSimplePublicObjectWithErrors || $response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process create batch deals request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return false;
            }
            $this->logger->info(
                'Create batch deals request completed.',
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

    public function updateDeals($dealsProperties)
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
            $batchDeals = new BatchInputSimplePublicObjectBatchInput($batchInputSimplePublicObjectInputData);

            $response = $this->hubSpot->crm()->deals()->batchApi()->update($batchDeals);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof BatchResponseSimplePublicObjectWithErrors || $response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process update batch deals request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return false;
            }
            $this->logger->info(
                'Update batch deals request completed.',
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
            $response = $this->hubSpot->crm()->deals()->searchApi()->doSearch($request);
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

    public function createDeal($properties)
    {
        try {
            $model = new SimplePublicObjectInput(['properties' => $properties]);
            $response = $this->hubSpot->crm()->deals()->basicApi()->create($model);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process create deal request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return $responseArray;
            }
            $this->logger->info(
                'Create deal request completed.',
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

    public function updateDeal($id, $properties)
    {
        try {
            unset($properties['hs_object_id']);
            $model = new SimplePublicObjectInput(['properties' => $properties]);
            $response = $this->hubSpot->crm()->deals()->basicApi()->update($id, $model);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process update deal request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return $responseArray;
            }
            $this->logger->info(
                'Update deal request completed.',
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
