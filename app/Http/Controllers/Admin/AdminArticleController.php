<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class AdminArticleController extends Controller
{
    public function index()
    {
        $articles = Article::withTrashed()
            ->with('story')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.articles', compact('articles'));
    }

    public function edit(Article $article)
    {
        $article->load('story');
        return view('admin.article-edit', compact('article'));
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $updates = $request->only(['title', 'destination', 'meta_title', 'meta_description', 'template_type', 'content']);

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('articles', 'public');
            $updates['cover_image'] = Storage::disk('public')->url($path);
        } elseif ($request->filled('cover_image_url')) {
            $updates['cover_image'] = $request->input('cover_image_url');
        }

        $article->update($updates);

        if ($request->filled('slides_json')) {
            $slides = json_decode($request->input('slides_json'), true);
            if (is_array($slides)) {
                $article->story()->updateOrCreate(
                    ['article_id' => $article->id],
                    ['slides' => $slides]
                );
            }
        }

        return redirect()->route('admin.articles.edit', $article)->with('success', 'Artigo atualizado.');
    }

    public function publish(Article $article): RedirectResponse
    {
        $article->update(['status' => 'published', 'published_at' => now()]);
        return redirect()->route('admin.articles.edit', $article)->with('success', 'Artigo publicado.');
    }

    public function unpublish(Article $article): RedirectResponse
    {
        $article->update(['status' => 'draft']);
        return redirect()->route('admin.articles.edit', $article)->with('success', 'Voltou para rascunho.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();
        return redirect()->route('admin.articles')->with('success', 'Artigo arquivado.');
    }

    public function restore(int $id): RedirectResponse
    {
        Article::withTrashed()->findOrFail($id)->restore();
        return back()->with('success', 'Artigo restaurado.');
    }
}
