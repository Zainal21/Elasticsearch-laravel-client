<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Elastic\Elasticsearch\Client;

class ReindexCommand extends Command
{
    protected $elasticSearch;

    public function __construct(Client $elasticSearch)
    {
        parent::__construct();
        $this->elasticSearch = $elasticSearch;
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:reindex';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Indexing all articles');
        
        foreach(Article::cursor() as $article){
            $this->elasticSearch->index([
                'index' => $article->getSearchIndex(),
                'type' => $article->getSearchType(),
                'id' => $article->getKey(),
                'body' => $article->toSearchArray(),
            ]);
            $this->output->write('.');
        }
        $this->info("\nDone");
    }
}
