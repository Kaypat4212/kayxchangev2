<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->get('category');

        $posts = BlogPost::published()
            ->when($category, fn($q) => $q->where('category', $category))
            ->paginate(9)
            ->withQueryString();

        $categories = BlogPost::published()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('blog.index', compact('posts', 'categories', 'category'));
    }

    public function show(string $slug)
    {
        $post = BlogPost::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $related = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->where('category', $post->category)
            ->limit(3)
            ->get();

        return view('blog.show', compact('post', 'related'));
    }
}
