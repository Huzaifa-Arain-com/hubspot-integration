<?php

namespace App\Console\Commands\HubSpot;

use App\Services\SetupHubspotService;
use Illuminate\Console\Command;

class Setup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hubspot:setup {--all} {--company} {--contact} {--deal}
    {--product} {--lineItem}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for initializing hubspot configurations.';

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
    public function handle(SetupHubspotService $setupHubspotService)
    {
        if ($this->option('all')) {
            $setupHubspotService->setupCompany();
            $setupHubspotService->setupContact();
            $setupHubspotService->setupDeal();
            $setupHubspotService->setupProduct();
            $setupHubspotService->setupLineItem();
        } elseif ($this->option('company')) {
            $setupHubspotService->setupCompany();
        } elseif ($this->option('contact')) {
            $setupHubspotService->setupContact();
        } elseif ($this->option('deal')) {
            $setupHubspotService->setupDeal();
        } elseif ($this->option('product')) {
            $setupHubspotService->setupProduct();
        } elseif ($this->option('lineItem')) {
            $setupHubspotService->setupLineItem();
        }

        return 0;
    }
}
