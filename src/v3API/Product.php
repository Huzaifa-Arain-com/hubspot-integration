<?php

namespace Markhor\HubspotIntegration\v3API;

use HubSpot\Client\Crm\Products\ApiException;
use HubSpot\Client\Crm\Products\Model\BatchInputSimplePublicObjectBatchInput;
use HubSpot\Client\Crm\Products\Model\BatchInputSimplePublicObjectInput;
use HubSpot\Client\Crm\Products\Model\BatchResponseSimplePublicObjectWithErrors;
use HubSpot\Client\Crm\Products\Model\Error;
use HubSpot\Client\Crm\Products\Model\Filter;
use HubSpot\Client\Crm\Products\Model\FilterGroup;
use HubSpot\Client\Crm\Products\Model\PublicObjectSearchRequest;
use HubSpot\Client\Crm\Products\Model\SimplePublicObjectBatchInput;
use HubSpot\Client\Crm\Products\Model\SimplePublicObjectInput;

class Product extends InitService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function createProducts($productsProperties)
    {
        try {
            $batchInputSimplePublicObjectInputData = ['inputs' => []];
            foreach ($productsProperties as $productProperties) {
                array_push(
                    $batchInputSimplePublicObjectInputData['inputs'],
                    new SimplePublicObjectInput(['properties' => $productProperties])
                );
            }
            $model = new BatchInputSimplePublicObjectInput($batchInputSimplePublicObjectInputData);

            $response = $this->hubSpot->crm()->products()->batchApi()->create($model);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof BatchResponseSimplePublicObjectWithErrors || $response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process create products request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return $responseArray;
            }
            $this->logger->info(
                'Create products request completed.',
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

    public function updateProducts($productsProperties)
    {
        try {
            $batchInputSimplePublicObjectInputData = ['inputs' => []];
            foreach ($productsProperties as $productProperties) {
                $id = $productProperties['hs_object_id'];
                unset($productProperties['hs_object_id']);
                array_push(
                    $batchInputSimplePublicObjectInputData['inputs'],
                    new SimplePublicObjectBatchInput(['id' => $id, 'properties' => $productProperties])
                );
            }
            $model = new BatchInputSimplePublicObjectBatchInput($batchInputSimplePublicObjectInputData);
            $response = $this->hubSpot->crm()->products()->batchApi()->update($model);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof BatchResponseSimplePublicObjectWithErrors || $response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process update products request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return $responseArray;
            }
            $this->logger->info(
                'Update products request completed.',
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

            $response = $this->hubSpot->crm()->products()->searchApi()->doSearch($request);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process search request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return $responseArray;
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

    public function createProduct($properties)
    {
        try {
            $model = new SimplePublicObjectInput(['properties' => $properties]);
            $response = $this->hubSpot->crm()->products()->basicApi()->create($model);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process create product request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return $responseArray;
            }
            $this->logger->info(
                'Create product request completed.',
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

    public function updateProduct($id, $properties)
    {
        try {
            unset($properties['hs_object_id']);
            $model = new SimplePublicObjectInput(['properties' => $properties]);
            $response = $this->hubSpot->crm()->products()->basicApi()->update($id, $model);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process update product request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return $responseArray;
            }
            $this->logger->info(
                'Update product request completed.',
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
