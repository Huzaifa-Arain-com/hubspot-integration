<?php

namespace App\Services;

use App\Models\Deal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Markhor\HubspotIntegration\Facades\v3\V3Association;
use Markhor\HubspotIntegration\Facades\v3\V3Deal;

class DealSyncService
{
    private $_logger = null;

    public function __construct()
    {
        $this->_logger = Log::channel(config('hubspot-integration.log_channel'));
    }

    public function processAll()
    {
        try {
            $dealsQuery = Deal::query();
            $dealsQuery
                ->with([
                    'associations',
                    'associations.associateable',
                    'associations.associateable.contacts',
                    'lineItems',
                    'lineItems.product',
                ])
                ->whereNull('synched_at')
                ->whereNull('failed_at');
            $dealsQuery->chunkById(100, function (Collection $dealCollection) {
                $createables = $dealCollection->filter(fn ($deal) => empty($deal->hs_object_id));
                $updateables = $dealCollection->filter(fn ($deal) => ! empty($deal->hs_object_id));
                foreach ($createables->chunk(10) as $createableChunk) {
                    $this->createBatch($createableChunk);
                    $this->associateDealToCompany($createableChunk);
                    $this->associateDealToContact($createableChunk);
                }
                foreach ($updateables->chunk(10) as $updateableChunk) {
                    $this->updateBatch($updateableChunk);
                    $this->associateDealToCompany($updateableChunk);
                    $this->associateDealToContact($updateableChunk);
                }
            });
        } catch (\Throwable $th) {
            $this->_logger->error($th->getMessage(), [__METHOD__]);
            report($th);
        }
    }

    private function createBatch(&$createableChunk)
    {
        try {
            $payload = $createableChunk->map(function ($deal) use ($createableChunk) {
                $searchRespone = V3Deal::search([[[
                    'value' => $deal->id,
                    'property' => 'mid_id',
                    'operator' => 'EQ',
                ]]]);
                if (! empty($searchRespone['results']) && empty($searchRespone['errors'])) {
                    if ($searchRespone['total'] > 1) {
                        $this->_logger->warning(
                            sprintf(
                                'Found multiple deals by "%s". Picking first one.',
                                $deal->id
                            ),
                            [__METHOD__, $deal]
                        );
                    }
                    $deal->hs_object_id = $searchRespone['results'][0]['id'] ?? null;
                    $deal->save();
                    $createableChunk = $createableChunk->except([$deal->id]);

                    return false;
                }

                return [
                    'mid_id' => $deal->id,
                    'dealname' => $deal->deal_name,
                    'pipeline' => $deal->pipeline,
                    'dealstage' => $deal->deal_stage,
                    'amount' => $deal->amount,
                ];
            })->filter()->values();
            if ($payload->count() > 0) {
                $response = V3Deal::createDeals($payload->toArray());
                if (! empty($response['results']) && empty($response['errors'])) {
                    foreach ($response['results'] as $result) {
                        $createableChunk
                            ->firstWhere('deal_name', $result['properties']['dealname'])
                            ->update([
                                'hs_object_id' => $result['id'],
                                'synched_at' => Carbon::now(),
                                'failed_at' => null,
                            ]);
                    }
                } else {
                    $createableChunk->each(function ($deal) {
                        $deal->synched_at = null;
                        $deal->failed_at = Carbon::now();
                        $deal->save();
                    });
                }
            }
        } catch (\Throwable $th) {
            $this->_logger->error($th->getMessage(), [__METHOD__]);
            report($th);
        }
    }

    private function updateBatch(&$updateableChunk)
    {
        try {
            $payload = $updateableChunk->map(function ($deal) {
                return [
                    'hs_object_id' => $deal->hs_object_id,
                    'dealname' => $deal->deal_name,
                    'pipeline' => $deal->pipeline,
                    'dealstage' => $deal->deal_stage,
                    'amount' => $deal->amount,
                ];
            });
            if ($payload->count() > 0) {
                $response = V3Deal::updateDeals($payload->toArray());
                if (! empty($response['results']) && empty($response['errors'])) {
                    foreach ($response['results'] as $result) {
                        $updateableChunk
                            ->firstWhere('hs_object_id', $result['id'])
                            ->update([
                                'synched_at' => Carbon::now(),
                                'failed_at' => null,
                            ]);
                    }
                } else {
                    $updateableChunk->each(function ($deal) {
                        $deal->synched_at = null;
                        $deal->failed_at = Carbon::now();
                        $deal->save();
                    });
                }
            }
        } catch (\Throwable $th) {
            $this->_logger->error($th->getMessage(), [__METHOD__]);
            report($th);
        }
    }

    private function associateDealToCompany(&$chunk)
    {
        try {
            $payload = $chunk->map(function ($deal) {
                return $deal->associations->map(function ($association) use ($deal) {
                    if (empty($deal->hs_object_id) || empty($association->associateable->hs_object_id)) {
                        return false;
                    }

                    return [
                        'from' => $deal->hs_object_id,
                        'to' => $association->associateable->hs_object_id,
                        'type' => 'deal_to_company',
                    ];
                });
            })->values()->flatten(1)->filter();
            if ($payload->count() > 0) {
                $response = V3Association::batchAssociate('deal', 'company', $payload->toArray());
                if (! empty($response['results']) && empty($response['errors'])) {
                } else {
                    $chunk->each(function ($contact) {
                        $contact->synched_at = null;
                        $contact->failed_at = Carbon::now();
                        $contact->save();
                    });
                }
            }
        } catch (\Throwable $th) {
            $this->_logger->error($th->getMessage(), [__METHOD__]);
            report($th);
        }
    }

    private function associateDealToContact(&$chunk)
    {
        try {
            $payload = $chunk->map(function ($deal) {
                return $deal->associations->map(function ($association) use ($deal) {
                    return $association->associateable->contacts->map(function ($contact) use ($deal) {
                        if (empty($deal->hs_object_id) || empty($contact->hs_object_id)) {
                            return false;
                        }

                        return [
                            'from' => $deal->hs_object_id,
                            'to' => $contact->hs_object_id,
                            'type' => 'deal_to_contact',
                        ];
                    });
                })->flatten(1);
            })->values()->flatten(1)->filter();
            if ($payload->count() > 0) {
                $response = V3Association::batchAssociate('deal', 'contact', $payload->toArray());
                if (! empty($response['results']) && empty($response['errors'])) {
                } else {
                    $chunk->each(function ($contact) {
                        $contact->synched_at = null;
                        $contact->failed_at = Carbon::now();
                        $contact->save();
                    });
                }
            }
        } catch (\Throwable $th) {
            $this->_logger->error($th->getMessage(), [__METHOD__]);
            report($th);
        }
    }

    public function processFailedRecords()
    {
        try {
            $dealsQuery = Deal::query();
            $dealsQuery
                ->whereNull('synched_at')
                ->whereNotNull('failed_at');
            $dealsQuery->chunkById(100, function (Collection $dealCollection) {
                $dealCollection->each(function ($deal) {
                    $payload = [
                        'mid_id' => $deal->id,
                        'hs_object_id' => $deal->hs_object_id,
                        'dealname' => $deal->deal_name,
                        'pipeline' => $deal->pipeline,
                        'dealstage' => $deal->deal_stage,
                        'amount' => $deal->amount,
                    ];
                    $response = false;
                    if (empty($deal->hs_object_id)) {
                        $response = V3Deal::createDeal($payload);
                    } else {
                        $response = V3Deal::updateDeal($deal->hs_object_id, $payload);
                    }
                    if (! empty($response['id']) && ! empty($response['properties'])) {
                        $deal->failed_at = null;
                        $deal->synched_at = Carbon::now();
                        $this->associateDealToCompany(collect([$deal]));
                        $this->associateDealToContact(collect([$deal]));
                        $deal->save();
                    }
                });
            });
        } catch (\Throwable $th) {
            $this->_logger->error($th->getMessage(), [__METHOD__]);
            report($th);
        }
    }
}
