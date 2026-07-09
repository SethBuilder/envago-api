<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebStory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminWebStoryController extends Controller
{
    public function index()
    {
        $stories = WebStory::whereHas('article')
            ->with('article')
            ->orderByDesc('created_at')
            ->get();
        return view('admin.stories', compact('stories'));
    }

    public function edit(WebStory $story)
    {
        $story->load('article');
        if (!$story->article) {
            return redirect()->route('admin.stories')->with('error', 'O artigo desta story foi arquivado.');
        }
        return view('admin.story-edit', compact('story'));
    }

    public function update(Request $request, WebStory $story): RedirectResponse
    {
        // Poster image
        if ($request->hasFile('poster_image')) {
            $path = $request->file('poster_image')->store('stories/posters', 'public');
            $story->poster_image = Storage::disk('public')->url($path);
        } elseif ($request->filled('poster_image_url')) {
            $story->poster_image = $request->input('poster_image_url');
        }

        // Per-slide images
        $slides = $story->slides;
        foreach ($slides as $i => &$slide) {
            if ($request->hasFile("slide_image_{$i}")) {
                $path = $request->file("slide_image_{$i}")->store('stories/slides', 'public');
                $slide['image_url'] = Storage::disk('public')->url($path);
            } elseif ($request->filled("slide_image_url_{$i}")) {
                $slide['image_url'] = $request->input("slide_image_url_{$i}");
            }
        }
        unset($slide);

        $story->slides = $slides;
        $story->save();

        return redirect()->route('admin.stories.edit', $story)->with('success', 'Story atualizada.');
    }

    public function publish(WebStory $story): RedirectResponse
    {
        if (!$story->poster_image) {
            return back()->with('error', 'Adicione uma imagem poster antes de publicar.');
        }

        $missing = collect($story->slides)->filter(fn($s) => empty($s['image_url']))->count();
        if ($missing > 0) {
            return back()->with('error', "{$missing} slide(s) sem imagem. Adicione todas as imagens antes de publicar.");
        }

        $story->update(['status' => 'published', 'published_at' => now()]);
        return back()->with('success', 'Story publicada.');
    }

    public function unpublish(WebStory $story): RedirectResponse
    {
        $story->update(['status' => 'draft', 'published_at' => null]);
        return back()->with('success', 'Story despublicada.');
    }
}
