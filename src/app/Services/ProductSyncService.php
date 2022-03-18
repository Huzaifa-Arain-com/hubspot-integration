<?php

namespace App\Services;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Markhor\HubspotIntegration\Facades\v3\V3Product;

class ProductSyncService
{
    private $_logger = null;

    public function __construct()
    {
        $this->_logger = Log::channel(config('hubspot-integration.log_channel'));
    }

    public function processAll()
    {
        try {
            $productsQuery = Product::query();
            $productsQuery
                ->whereNull('synched_at')
                ->whereNull('failed_at');
            $productsQuery->chunkById(100, function (Collection $productCollection) {
                $createables = $productCollection->filter(fn ($product) => empty($product->hs_object_id));
                $updateables = $productCollection->filter(fn ($product) => ! empty($product->hs_object_id));
                foreach ($createables->chunk(10) as $createableChunk) {
                    $this->createBatch($createableChunk);
                }
                foreach ($updateables->chunk(10) as $updateableChunk) {
                    $this->updateBatch($updateableChunk);
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
            $payload = $createableChunk->map(function ($product) use ($createableChunk) {
                $searchRespone = V3Product::search([[[
                    'value' => $product->id,
                    'property' => 'mid_id',
                    'operator' => 'EQ',
                ]]]);
                if (! empty($searchRespone['results'])) {
                    if ($searchRespone['total'] > 1) {
                        $this->_logger->warning(
                            sprintf(
                                'Found multiple products by "%s". Picking first one.',
                                $product->id
                            ),
                            [__METHOD__, $product]
                        );
                    }
                    $product->hs_object_id = $searchRespone['results'][0]['id'] ?? null;
                    $product->save();
                    $createableChunk = $createableChunk->except([$product->id]);

                    return false;
                }

                return [
                    'mid_id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'hs_price_usd' => $product->price,
                ];
            })->filter()->values();
            if ($payload->count() > 0) {
                $response = V3Product::createProducts($payload->toArray());
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
                    $createableChunk->each(function ($product) {
                        $product->synched_at = null;
                        $product->failed_at = Carbon::now();
                        $product->save();
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
            $payload = $updateableChunk->map(function ($product) {
                return [
                    'hs_object_id' => $product->hs_object_id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'hs_price_usd' => $product->price,
                ];
            });
            if ($payload->count() > 0) {
                $response = V3Product::updateProducts($payload->toArray());
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
                    $updateableChunk->each(function ($product) {
                        $product->synched_at = null;
                        $product->failed_at = Carbon::now();
                        $product->save();
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
            $productsQuery = Product::query();
            $productsQuery
                ->whereNull('synched_at')
                ->whereNotNull('failed_at');
            $productsQuery->chunkById(100, function (Collection $productCollection) {
                $productCollection->each(function ($product) {
                    $payload = [
                        'mid_id' => $product->id,
                        'hs_object_id' => $product->hs_object_id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'hs_price_usd' => $product->price,
                    ];
                    $response = false;
                    if (empty($product->hs_object_id)) {
                        $response = V3Product::createProduct($payload);
                    } else {
                        $response = V3Product::updateProduct($product->hs_object_id, $payload);
                    }
                    if (! empty($response['id']) && ! empty($response['properties'])) {
                        $product->failed_at = null;
                        $product->synched_at = Carbon::now();
                        $product->save();
                    }
                });
            });
        } catch (\Throwable $th) {
            $this->_logger->error($th->getMessage(), [__METHOD__]);
            report($th);
        }
    }
}
