<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use App\Http\Repositories\ArticleRepository;

class ArticleController extends Controller
{

    protected $articleRepository;

    public function __construct()
    {
        $this->articleRepository = new ArticleRepository();
    }
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $articles = request()->has('q')
             ? $this->articleRepository->searchArticle(request('q'))
             : Article::paginate(50);
        return response()->json([
            'articles' => $articles,
            'success' => true
        ], 200);
    }
}
