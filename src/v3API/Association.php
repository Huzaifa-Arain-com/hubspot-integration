<?php

namespace Markhor\HubspotIntegration\v3API;

use HubSpot\Client\Crm\Associations\ApiException;
use HubSpot\Client\Crm\Associations\Model\BatchInputPublicAssociation;
use HubSpot\Client\Crm\Associations\Model\BatchInputPublicObjectId;
use HubSpot\Client\Crm\Associations\Model\Error;
use HubSpot\Client\Crm\Associations\Model\PublicAssociation;

class Association extends InitService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Associate objects.
     *
     * Example :
     *```php
     *batchAssociate('deal','contact',[[
     *  'from' => value,
     *  'to' => value,
     *  'type' => 'deal_to_contact'
     *]])
     *```
     * @param string $fromObjectType
     * @param string $toObjectType
     * @param array $entities Multidimensional array of entities
     * @return bool|array
     */
    public function batchAssociate($fromObjectType, $toObjectType, array $entities)
    {
        try {
            $inputs = array_map(function ($entity) {
                foreach ($entity as $index => $attr) {
                    $entity[$index] = strval($attr);
                }

                return new PublicAssociation($entity);
            }, $entities);
            $batchInputPublicAssociates = new BatchInputPublicAssociation(['inputs' => $inputs]);
            $response = $this->hubSpot->crm()->associations()->batchApi()->create(
                $fromObjectType,
                $toObjectType,
                $batchInputPublicAssociates
            );
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process batch associate request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return false;
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

    public function readBatchAssociations($objectIds, $fromObjectType, $toObjectType)
    {
        try {
            $batchInputPublicObjectIds = new BatchInputPublicObjectId(['inputs' => $objectIds]);
            $response = $this->hubSpot->crm()->associations()->batchApi()->read(
                $fromObjectType,
                $toObjectType,
                $batchInputPublicObjectIds
            );
            $responseArray = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process read batch associations request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return false;
            }
            $this->logger->info(
                'Read batch associations request completed.',
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

    public function listAssociationTypes($fromObjectType, $toObjectType)
    {
        try {
            $response = $this->hubSpot->crm()->associations()->typesApi()->getAll($fromObjectType, $toObjectType);
            $responseArray = $response = json_decode($response->__toString(), true);
            if ($response instanceof Error) {
                $this->logger->error(
                    'Couldn\'t process list associations request.',
                    [__METHOD__, $responseArray['errors'], func_get_args()]
                );

                return false;
            }
            $this->logger->info(
                'List associations request completed.',
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
