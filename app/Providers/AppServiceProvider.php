<?php

namespace App\Providers;

use Elastic\Elasticsearch\Client;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Elastic\Elasticsearch\ClientBuilder;
use App\Http\Repositories\ArticleRepository;
use App\Http\Repositories\ElasticsearchRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if(config('app.env') == 'production') {
            URL::forceScheme('https');
        }
        
        $this->app->bind(ArticleRepository::class, function ($app) {
            if (! config('services.search.enabled')) {
                return new EloquentRepository();
            }

            return new ElasticsearchRepository(
                $app->make(Client::class)
            );
        });
    }

    private function bindSearchClient()
    {
        $this->app->bind(Client::class, function($app){
            return ClientBuilder::create()
                    ->setHosts($app['config'])->get('services.search.hosts')->build();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
