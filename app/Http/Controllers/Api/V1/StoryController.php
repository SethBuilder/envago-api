<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WebStory;

class StoryController extends Controller
{
    public function index()
    {
        $stories = WebStory::where('status', 'published')
            ->whereHas('article')
            ->with(['article:id,slug,title,destination,status'])
            ->orderByDesc('published_at')
            ->get()
            ->map(fn($s) => [
                'article_slug'        => $s->article?->slug ?? '',
                'article_title'       => $s->article?->title ?? '',
                'article_destination' => $s->article?->destination,
                'poster_image'        => $s->poster_image,
                'slide_count'         => count($s->slides),
                'published_at'        => $s->published_at,
            ]);

        return response()->json(['data' => $stories]);
    }

    public function show(string $slug)
    {
        $story = WebStory::where('status', 'published')
            ->whereHas('article', fn($q) => $q->where('slug', $slug))
            ->with(['article:id,slug,title,destination,status,published_at'])
            ->firstOrFail();

        return response()->json([
            'data' => [
                'slides'       => $story->slides,
                'poster_image' => $story->poster_image,
                'published_at' => $story->published_at,
                'article'      => [
                    'slug'        => $story->article->slug,
                    'title'       => $story->article->title,
                    'destination' => $story->article->destination,
                    'published'   => $story->article->status === 'published',
                ],
            ],
        ]);
    }
}
