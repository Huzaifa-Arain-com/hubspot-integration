<?php

namespace App\Services;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Markhor\HubspotIntegration\Facades\v3\V3Company;

class CompanySyncService
{
    private $_logger = null;

    public function __construct()
    {
        $this->_logger = Log::channel(config('hubspot-integration.log_channel'));
    }

    public function processAll()
    {
        try {
            $companiesQuery = Company::query();
            $companiesQuery
                ->whereNull('synched_at')
                ->whereNull('failed_at');
            $companiesQuery->chunkById(100, function (Collection $companyCollection) {
                $createables = $companyCollection->filter(fn ($company) => empty($company->hs_object_id));
                $updateables = $companyCollection->filter(fn ($company) => ! empty($company->hs_object_id));
                foreach ($createables->chunk(10) as $createableChunk) {
                    $this->createBatch($createableChunk);
                }
                foreach ($updateables->chunk(10) as $updateableChunk) {
                    $this->updateBatch($updateableChunk);
                }
            });
        } catch (\Throwable $th) {
            $this->_logger->error($th->getMessage(), [__METHOD__, $th->getTrace()]);
            report($th);
        }
    }

    private function createBatch(&$createableChunk)
    {
        try {
            $payload = $createableChunk->map(function ($company) use ($createableChunk) {
                $searchRespone = V3Company::search([[[
                    'value' => $company->name,
                    'property' => 'name',
                    'operator' => 'EQ',
                ]]]);
                if (! empty($searchRespone['results']) && empty($searchRespone['errors'])) {
                    if ($searchRespone['total'] > 1) {
                        $this->_logger->warning(
                            sprintf(
                                'Found multiple companies by "%s". Picking first one.',
                                $company->name
                            ),
                            [__METHOD__, $company]
                        );
                    }
                    $company->hs_object_id = $searchRespone['results'][0]['id'] ?? null;
                    $company->save();
                    $createableChunk = $createableChunk->except([$company->id]);

                    return false;
                }

                return [
                    'mid_id' => $company->id,
                    'name' => $company->name,
                ];
            })->filter()->values();
            if ($payload->count() > 0) {
                $response = V3Company::createCompanies($payload->toArray());
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
                    $createableChunk->each(function ($company) {
                        $company->synched_at = null;
                        $company->failed_at = Carbon::now();
                        $company->save();
                    });
                }
            }
        } catch (\Throwable $th) {
            $this->_logger->error($th->getMessage(), [__METHOD__, $th->getTrace()]);
            report($th);
        }
    }

    private function updateBatch(&$updateableChunk)
    {
        try {
            $payload = $updateableChunk->map(function ($company) {
                return [
                    'hs_object_id' => $company->hs_object_id,
                    'name' => $company->name,
                ];
            });
            if ($payload->count() > 0) {
                $response = V3Company::updateCompanies($payload->toArray());
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
                    $updateableChunk->each(function ($company) {
                        $company->synched_at = null;
                        $company->failed_at = Carbon::now();
                        $company->save();
                    });
                }
            }
        } catch (\Throwable $th) {
            $this->_logger->error($th->getMessage(), [__METHOD__, $th->getTrace()]);
            report($th);
        }
    }

    public function processFailedRecords()
    {
        try {
            $companiesQuery = Company::query();
            $companiesQuery
                ->whereNull('synched_at')
                ->whereNotNull('failed_at');
            $companiesQuery->chunkById(100, function (Collection $companyCollection) {
                $companyCollection->each(function ($company) {
                    $payload = [
                        'mid_id' => $company->id,
                        'hs_object_id' => $company->hs_object_id,
                        'name' => $company->name,
                    ];
                    $response = false;
                    if (! isset($company->hs_object_id)) {
                        $response = V3Company::createCompany($payload);
                    } else {
                        $response = V3Company::updateCompany($company->hs_object_id, $payload);
                    }
                    if (! empty($response['id']) && ! empty($response['properties'])) {
                        $company->failed_at = null;
                        $company->synched_at = Carbon::now();
                        $company->save();
                    }
                });
            });
        } catch (\Throwable $th) {
            $this->_logger->error($th->getMessage(), [__METHOD__, $th->getTrace()]);
            report($th);
        }
    }
}
