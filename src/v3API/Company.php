<?php

namespace Markhor\HubspotIntegration\v3API;

use HubSpot\Client\Crm\Companies\ApiException;
use HubSpot\Client\Crm\Companies\Model\BatchInputSimplePublicObjectBatchInput;
use HubSpot\Client\Crm\Companies\Model\BatchInputSimplePublicObjectInput;
use HubSpot\Client\Crm\Companies\Model\BatchReadInputSimplePublicObjectId;
use HubSpot\Client\Crm\Companies\Model\BatchResponseSimplePublicObjectWithErrors;
use HubSpot\Client\Crm\Companies\Model\Error;
use HubSpot\Client\Crm\Companies\Model\Filter;
use HubSpot\Client\Crm\Companies\Model\FilterGroup;
use HubSpot\Client\Crm\Companies\Model\PublicObjectSearchRequest;
use HubSpot\Client\Crm\Companies\Model\SimplePublicObjectBatchInput;
use HubSpot\Client\Crm\Companies\Model\SimplePublicObjectInput;

class Company extends InitService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function createCompanies($companiesProperties)
    {
        try {
            $batchInputSimplePublicObjectInputData = ['inputs' => []];
            foreach ($companiesProperties as $companyProperties) {
                unset($companyProperties['hs_object_id']);
                array_push(
                    $batchInputSimplePublicObjectInputData['inputs'],
                    new SimplePublicObjectInput(['properties' => $companyProperties])
                );
            }

            $batchCompanies = new BatchInputSimplePublicObjectInput($batchInputSimplePublicObjectInputData);

            $response = $this->hubSpot->crm()->companies()->batchApi()->create($batchCompanies);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof BatchResponseSimplePublicObjectWithErrors || $response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process create companies request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return $responseArray;
            }
            $this->logger->info(
                'Create companies request completed.',
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

    public function updateCompanies($companiesProperties)
    {
        try {
            $batchInputSimplePublicObjectInputData = ['inputs' => []];
            foreach ($companiesProperties as $companyProperties) {
                $id = $companyProperties['hs_object_id'];
                unset($companyProperties['hs_object_id']);
                array_push(
                    $batchInputSimplePublicObjectInputData['inputs'],
                    new SimplePublicObjectBatchInput(['id' => $id, 'properties' => $companyProperties])
                );
            }
            $batchCompanies = new BatchInputSimplePublicObjectBatchInput($batchInputSimplePublicObjectInputData);

            $response = $this->hubSpot->crm()->companies()->batchApi()->update($batchCompanies);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof BatchResponseSimplePublicObjectWithErrors || $response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process update companies request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return $responseArray;
            }
            $this->logger->info(
                'Update companies request completed.',
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

    public function listCompanies(
        $limit = 10,
        $after = null,
        $properties = null,
        $associations = null,
        $archived = false
    ) {
        try {
            $response = $this->hubSpot->crm()->companies()->basicApi()->getPage(
                $limit,
                $after,
                $properties,
                $associations,
                $archived
            );
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process list companies request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return $responseArray;
            }
            $this->logger->info(
                'List companies request completed.',
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
            $response = $this->hubSpot->crm()->companies()->searchApi()->doSearch($request);
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

    public function getCompanyById(
        $company_id,
        $properties = null,
        $associations = null,
        $archived = false,
        $id_property = null
    ) {
        try {
            $response = $this->hubSpot->crm()->companies()->basicApi()->getById(
                $company_id,
                $properties,
                $associations,
                $archived,
                $id_property
            );
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    '',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return $responseArray;
            }
            $this->logger->info(
                'Batch associate request completed.',
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

    public function createCompany($properties)
    {
        try {
            $model = new SimplePublicObjectInput(['properties' => $properties]);
            $response = $this->hubSpot->crm()->companies()->basicApi()->create($model);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process create company request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return $responseArray;
            }
            $this->logger->info(
                'Create company request completed.',
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

    public function updateCompany($id, $properties)
    {
        try {
            unset($properties['hs_object_id']);
            $model = new SimplePublicObjectInput(['properties' => $properties]);
            $response = $this->hubSpot->crm()->companies()->basicApi()->update($id, $model);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process update company request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return $responseArray;
            }
            $this->logger->info(
                'Update company request completed.',
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

    public function readBatchByPropertyValue(
        $propertyName,
        array $propertyValues = null,
        array $responseProperties
    ) {
        try {
            $ids = array_map(function ($id) {
                return ['id' => $id];
            }, $propertyValues);
            $model = new BatchReadInputSimplePublicObjectId([
                'properties' => $responseProperties,
                'id_property' => $propertyName,
                'inputs' => $ids,
            ]);
            $response = $this->hubSpot->crm()->companies()->batchApi()->read($model);
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t read batch of companies request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return $responseArray;
            }
            $this->logger->info(
                'Read batch of companies request completed.',
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
