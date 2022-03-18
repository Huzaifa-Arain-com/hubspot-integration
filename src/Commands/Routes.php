<?php

namespace Markhor\HubspotIntegration\Commands;

use Illuminate\Console\Command;

class Routes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'markhor:routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for copying routes from package.';

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
    public function handle()
    {
        try {
            $contents = file_get_contents(base_path('routes/web.php'));
            $routes = file_get_contents(__DIR__ . '/../../routes/web.stub');
            if (! str_contains($contents, $routes)) {
                $contents = $contents . "\n" . $routes;
                file_put_contents(base_path('routes/web.php'), $contents);
                $this->output->writeln('Routes has been copied to routes/web.php');

                return 1;
            }
            $this->output->writeln('routes/web.php already up to date.');
        } catch (\Throwable $th) {
            throw $th;
        }

        return 0;
    }
}
