<?php

namespace App\Http\Repositories;

use App\Models\Article;
use Illuminate\Http\Request;
use Elastic\Elasticsearch\Client;
use Illuminate\Database\Eloquent\Collection;

interface ArticleInterface
{
    public function searchArticle($query);
}

class ArticleRepository implements ArticleInterface
{
    protected $elasticsearch;

    public function __construct(Client $elasticsearch)
    {
        $this->elasticsearch = $elasticsearch;
    }

    public function searchArticle($query)
    {
        return Article::query()
            ->where(fn ($query) => (
                $query->where('body', 'LIKE', "%{$query}%")
                    ->orWhere('title', 'LIKE', "%{$query}%")
            ))
            ->get();
    }

    private function searchOnElasticsearch(string $query = ''): array
    {
        $model = new Article;

        $items = $this->elasticsearch->search([
            'index' => $model->getSearchIndex(),
            'type' => $model->getSearchType(),
            'body' => [
                'query' => [
                    'multi_match' => [
                        'fields' => ['title^5', 'body', 'tags'],
                        'query' => $query,
                    ],
                ],
            ],
        ]);

        return $items;
    }

    private function buildCollection(array $items): Collection
    {
        $ids = Arr::pluck($items['hits']['hits'], '_id');

        return Article::findMany($ids)
            ->sortBy(function ($article) use ($ids) {
                return array_search($article->getKey(), $ids);
            });
    }
}
