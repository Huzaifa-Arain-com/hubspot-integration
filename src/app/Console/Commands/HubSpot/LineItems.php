<?php

namespace App\Console\Commands\HubSpot;

use App\Services\LineItemSyncService;
use Illuminate\Console\Command;

class LineItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hubspot:lineItems {--post} {--all} {--failed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for interacting with hubspot line items';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(LineItemSyncService $lineItemSyncService)
    {
        if ($this->option('post')) {
            if ($this->option('all')) {
                $lineItemSyncService->processAll();
            } elseif ($this->option('failed')) {
                $lineItemSyncService->processFailedRecords();
            }
        }

        return 0;
    }
}
