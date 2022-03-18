<?php

namespace App\Services;

use App\Models\Contact;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Markhor\HubspotIntegration\Facades\v3\V3Association;
use Markhor\HubspotIntegration\Facades\v3\V3Contact;

class ContactSyncService
{
    private $_logger = null;

    public function __construct()
    {
        $this->_logger = Log::channel(config('hubspot-integration.log_channel'));
    }

    public function processAll()
    {
        try {
            $contactsQuery = Contact::query();
            $contactsQuery
                ->whereNull('synched_at')
                ->whereNull('failed_at');
            $contactsQuery->chunkById(100, function (Collection $contactCollection) {
                $createables = $contactCollection->filter(fn ($contact) => empty($contact->hs_object_id));
                $updateables = $contactCollection->filter(fn ($contact) => ! empty($contact->hs_object_id));
                foreach ($createables->chunk(10) as $createableChunk) {
                    $this->createBatch($createableChunk);
                    $this->associateContactToCompany($createableChunk);
                }
                foreach ($updateables->chunk(10) as $updateableChunk) {
                    $this->updateBatch($updateableChunk);
                    $this->associateContactToCompany($updateableChunk);
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
            $payload = $createableChunk->map(function ($contact) use ($createableChunk) {
                $searchRespone = V3Contact::search([[[
                    'value' => $contact->email,
                    'property' => 'email',
                    'operator' => 'EQ',
                ]]]);
                if (! empty($searchRespone['results']) && empty($searchRespone['errors'])) {
                    if ($searchRespone['total'] > 1) {
                        $this->_logger->warning(
                            sprintf(
                                'Found multiple contacts by "%s". Picking first one.',
                                $contact->email
                            ),
                            [__METHOD__, $contact]
                        );
                    }
                    $contact->hs_object_id = $searchRespone['results'][0]['id'] ?? null;
                    $contact->save();
                    $createableChunk = $createableChunk->except([$contact->id]);

                    return false;
                }

                return [
                    'mid_id' => $contact->id,
                    'firstname' => $contact->first_name,
                    'lastname' => $contact->last_name,
                    'email' => $contact->email,
                ];
            })->filter()->values();
            if ($payload->count() > 0) {
                $response = V3Contact::createContacts($payload->toArray());
                if (! empty($response['results']) && empty($response['errors'])) {
                    foreach ($response['results'] as $result) {
                        $createableChunk
                            ->firstWhere('email', $result['properties']['email'])
                            ->update([
                                'hs_object_id' => $result['id'],
                                'synched_at' => Carbon::now(),
                                'failed_at' => null,
                            ]);
                    }
                } else {
                    $createableChunk->each(function ($contact) {
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

    private function updateBatch(&$updateableChunk)
    {
        try {
            $payload = $updateableChunk->map(function ($contact) {
                return [
                    'hs_object_id' => $contact->hs_object_id,
                    'firstname' => $contact->first_name,
                    'lastname' => $contact->last_name,
                    'email' => $contact->email,
                ];
            });
            if ($payload->count() > 0) {
                $response = V3Contact::updateContacts($payload->toArray());
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
                    $updateableChunk->each(function ($contact) {
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

    private function associateContactToCompany(&$chunk)
    {
        try {
            $payload = $chunk->map(function ($contact) {
                if (! empty($contact->hs_object_id) || ! empty($contact->company->hs_object_id)) {
                    $this->_logger->warning(
                        'Contact/Company not synched.',
                        [__METHOD__, $contact->toArray()]
                    );

                    return false;
                }

                return [
                    'from' => $contact->hs_object_id,
                    'to' => $contact->company->hs_object_id,
                    'type' => 'contact_to_company',
                ];
            })->filter()->values();
            if ($payload->count() > 0) {
                $response = V3Association::batchAssociate('contact', 'company', $payload->toArray());
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
            $contactsQuery = Contact::query();
            $contactsQuery
                ->whereNull('synched_at')
                ->whereNotNull('failed_at');
            $contactsQuery->chunkById(100, function (Collection $contactCollection) {
                $contactCollection->each(function ($contact) {
                    $payload = [
                        'mid_id' => $contact->id,
                        'hs_object_id' => $contact->hs_object_id,
                        'firstname' => $contact->first_name,
                        'lastname' => $contact->last_name,
                        'email' => $contact->email,
                    ];
                    $response = false;
                    if (empty($contact->hs_object_id)) {
                        $response = V3Contact::createContact($payload);
                    } else {
                        $response = V3Contact::updateContact($contact->hs_object_id, $payload);
                    }
                    if (! empty($response['id']) && ! empty($response['properties'])) {
                        $contact->failed_at = null;
                        $contact->synched_at = Carbon::now();
                        $this->associateContactToCompany(collect([$contact]));
                        $contact->save();
                    }
                });
            });
        } catch (\Throwable $th) {
            $this->_logger->error($th->getMessage(), [__METHOD__]);
            report($th);
        }
    }
}
