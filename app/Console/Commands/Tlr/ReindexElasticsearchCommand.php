<?php

namespace App\Console\Commands\Tlr;

use App\Models\IndexConfigurators\ServicesIndexConfigurator;
use App\Models\Service;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Throwable;

class ReindexElasticsearchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tlr:reindex-elasticsearch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes the indices if they exist, recreates them, and then imports all data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (Config::get('scout.driver') !== 'elastic') {
            $this->warn('Did not reindex due to not using the [elastic] Scout driver.');

            return;
        }

        try {
            $this->line('Dropping index...');
            $this->call('elastic:drop-index', ['index-configurator' => ServicesIndexConfigurator::class]);
        } catch (Throwable $exception) {
            // If the index already does not exist then do nothing.
            $this->warn('Could not drop index, this is most likely due to the index not already existing.');
        }

        $this->line('Creating index...');
        $this->call('elastic:create-index', ['index-configurator' => ServicesIndexConfigurator::class]);

        $this->line('Updating index mapping...');
        $this->call('elastic:update-mapping', ['model' => Service::class]);

        $this->line('Importing models...');
        $this->call('tlr:scout-import', ['model' => Service::class]);
    }
}
