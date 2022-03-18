<?php

namespace App\Services;

use App\Models\DealLineItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Markhor\HubspotIntegration\Facades\v3\V3Association;
use Markhor\HubspotIntegration\Facades\v3\V3LineItem;

class LineItemSyncService
{
    private $_logger = null;

    public function __construct()
    {
        $this->_logger = Log::channel(config('hubspot-integration.log_channel'));
    }

    public function processAll()
    {
        try {
            $lineItemQuery = DealLineItem::query();
            $lineItemQuery
                ->with([
                    'deal',
                    'product',
                ])
                ->whereHas('deal', function ($query) {
                    $query->whereNotNull('hs_object_id');
                })
                ->whereNull('synched_at')
                ->whereNull('failed_at');
            $lineItemQuery->chunkById(100, function (Collection $lineItemCollection) {
                $createables = $lineItemCollection->filter(fn ($lineItem) => empty($lineItem->hs_object_id));
                // $updateables = $lineItemCollection->filter(fn ($lineItem) => isset($lineItem->hs_object_id));
                foreach ($createables->chunk(10) as $createableChunk) {
                    $this->createBatch($createableChunk);
                    $this->associateLineItemToDeal($createableChunk);
                }
                // foreach ($updateables->chunk(10) as $updateableChunk) {
                //     $this->updateBatch($updateableChunk);
                //     $this->associateLineItemToDeal($updateableChunk);
                // }
            });
        } catch (\Throwable $th) {
            $this->_logger->error($th->getMessage(), [__METHOD__]);
            report($th);
        }
    }

    private function createBatch(&$createableChunk)
    {
        try {
            $payload = $createableChunk->map(function ($lineItem) {
                return [
                    'mid_id' => $lineItem->id,
                    'hs_product_id' => $lineItem->product->hs_object_id,
                    'quantity' => $lineItem->quantity,
                ];
            });
            if ($payload->count() > 0) {
                $response = V3LineItem::createLineItems($payload->toArray());
                if (! empty($response['results']) && empty($response['errors'])) {
                    foreach ($response['results'] as $result) {
                        $createableChunk
                            ->firstWhere('id', $result['properties']['mid_id'])
                            ->update([
                                'hs_object_id' => $result['id'],
                                'synched_at' => Carbon::now(),
                                'failed_at' => null,
                            ]);
                    }
                } else {
                    $createableChunk->each(function ($lineItem) {
                        $lineItem->synched_at = null;
                        $lineItem->failed_at = Carbon::now();
                        $lineItem->save();
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
            $payload = $updateableChunk->map(function ($lineItem) {
                return [
                    'hs_object_id' => $lineItem->hs_object_id,
                    'hs_product_id' => $lineItem->product->hs_object_id,
                    'quantity' => $lineItem->quantity,
                ];
            });
            if ($payload->count() > 0) {
                $response = V3LineItem::updateLineItems($payload->toArray());
                if (! empty($response['results']) && empty($response['errors'])) {
                    foreach ($response['results'] as $result) {
                        $updateableChunk
                            ->firstWhere('hs_object_id', $result['id'])
                            ->update([
                                'synched_at' => Carbon::now(),
                            ]);
                    }
                } else {
                    $updateableChunk->each(function ($lineItem) {
                        $lineItem->failed_at = Carbon::now();
                        $lineItem->save();
                    });
                }
            }
        } catch (\Throwable $th) {
            $this->_logger->error($th->getMessage(), [__METHOD__]);
            report($th);
        }
    }

    private function associateLineItemToDeal(&$chunk)
    {
        try {
            $payload = $chunk->map(function ($lineItem) {
                return [
                    'from' => $lineItem->hs_object_id,
                    'to' => $lineItem->deal->hs_object_id,
                    'type' => 'line_item_to_deal',
                ];
            });
            if ($payload->count() > 0) {
                $response = V3Association::batchAssociate('line_item', 'deal', $payload->toArray());
                if (! empty($response['results']) && empty($response['errors'])) {
                } else {
                    $chunk->each(function ($lineItem) {
                        $lineItem->synched_at = null;
                        $lineItem->failed_at = Carbon::now();
                        $lineItem->save();
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
            $lineItemQuery = DealLineItem::query();
            $lineItemQuery
                ->whereNull('synched_at')
                ->whereNotNull('failed_at');
            $lineItemQuery->chunkById(100, function (Collection $lineItemCollection) {
                $lineItemCollection->each(function ($lineItem) {
                    $payload = [
                        'hs_object_id' => $lineItem->hs_object_id,
                        'hs_product_id' => $lineItem->product->hs_object_id,
                        'quantity' => $lineItem->quantity,
                    ];
                    $response = false;
                    if (! isset($lineItem->hs_object_id)) {
                        $response = V3LineItem::createLineItem($payload);
                    } else {
                        $response = V3LineItem::updateLineItem($lineItem->hs_object_id, $payload);
                    }
                    if (! empty($response['id']) && ! empty($response['properties'])) {
                        $lineItem->failed_at = null;
                        $lineItem->synched_at = Carbon::now();
                        $this->associateLineItemToDeal(collect([$lineItem]));
                        $lineItem->save();
                    }
                });
            });
        } catch (\Throwable $th) {
            $this->_logger->error($th->getMessage(), [__METHOD__]);
            report($th);
        }
    }
}
