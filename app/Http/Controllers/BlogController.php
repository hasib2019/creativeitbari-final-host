<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        return $this->listing($request);
    }

    public function category(Request $request, BlogCategory $category): View
    {
        abort_unless($category->is_active, 404);

        return $this->listing($request, $category);
    }

    private function listing(Request $request, ?BlogCategory $category = null): View
    {
        $search = trim((string) $request->query('q'));

        return view('pages.blog.index', [
            'posts' => Post::published()->with('category')
                ->when($category, fn ($q) => $q->where('blog_category_id', $category->id))
                ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")))
                ->latest('published_at')
                ->paginate(9)->withQueryString(),
            'categories' => BlogCategory::active()->ordered()
                ->withCount(['posts' => fn ($q) => $q->published()])->get(),
            'category' => $category,
            'search' => $search,
        ]);
    }

    public function show(Request $request, Post $post): View
    {
        abort_unless($post->is_published && (! $post->published_at || $post->published_at->isPast()), 404);

        // Count one view per visitor session.
        $viewed = $request->session()->get('viewed_posts', []);
        if (! in_array($post->id, $viewed, true)) {
            $post->increment('views');
            $request->session()->push('viewed_posts', $post->id);
        }

        $post->load('category', 'author');

        return view('pages.blog.show', [
            'post' => $post,
            'related' => Post::published()->with('category')
                ->whereKeyNot($post->id)
                ->when($post->blog_category_id, fn ($q) => $q->where('blog_category_id', $post->blog_category_id))
                ->latest('published_at')->take(3)->get(),
            'categories' => BlogCategory::active()->ordered()->withCount(['posts' => fn ($q) => $q->published()])->get(),
            'recent' => Post::published()->whereKeyNot($post->id)->latest('published_at')->take(4)->get(),
        ]);
    }

    public function feed(): Response
    {
        $posts = Post::published()->latest('published_at')->take(20)->get();

        return response()
            ->view('feeds.blog', ['posts' => $posts])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
