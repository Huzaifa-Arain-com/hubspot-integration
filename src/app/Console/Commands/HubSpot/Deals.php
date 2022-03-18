<?php

namespace App\Console\Commands\HubSpot;

use App\Services\DealSyncService;
use Illuminate\Console\Command;

class Deals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hubspot:deals {--post} {--all} {--failed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for interacting with hubspot deals';

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
    public function handle(DealSyncService $syncService)
    {
        if ($this->option('post')) {
            if ($this->option('all')) {
                $syncService->processAll();
            } elseif ($this->option('failed')) {
                $syncService->processFailedRecords();
            }
        }

        return 0;
    }
}
