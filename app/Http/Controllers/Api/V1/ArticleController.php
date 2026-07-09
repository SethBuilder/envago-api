<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Article;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::where('status', 'published')
            ->orderByDesc('published_at')
            ->get(['id', 'slug', 'title', 'meta_description', 'destination', 'template_type', 'cover_image', 'published_at']);

        return response()->json(['data' => $articles]);
    }

    public function show(string $slug)
    {
        $article = Article::where('slug', $slug)
            ->where('status', 'published')
            ->with('story')
            ->firstOrFail();

        return response()->json(['data' => $article]);
    }

    public function story(string $slug)
    {
        $article = Article::where('slug', $slug)
            ->where('status', 'published')
            ->with('story')
            ->firstOrFail();

        abort_unless($article->story, 404);

        return response()->json(['data' => $article->story]);
    }
}
