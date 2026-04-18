<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminBlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::orderByDesc('created_at')->paginate(15);
        return view('admin.blog.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.blog.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'category'     => 'required|string|max:100',
            'excerpt'      => 'nullable|string|max:500',
            'content'      => 'required|string',
            'cover_image'  => 'nullable|url|max:2048',
            'is_published' => 'nullable|boolean',
        ]);

        $data['slug']         = BlogPost::uniqueSlug($data['title']);
        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = $data['is_published'] ? now() : null;

        BlogPost::create($data);

        return redirect()->route('admin.blog.index')->with('success', 'Post created successfully.');
    }

    public function edit(BlogPost $post)
    {
        return view('admin.blog.edit', compact('post'));
    }

    public function update(Request $request, BlogPost $post)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'category'     => 'required|string|max:100',
            'excerpt'      => 'nullable|string|max:500',
            'content'      => 'required|string',
            'cover_image'  => 'nullable|url|max:2048',
            'is_published' => 'nullable|boolean',
        ]);

        $data['is_published'] = $request->boolean('is_published');

        // Set published_at when first publishing
        if ($data['is_published'] && !$post->is_published) {
            $data['published_at'] = now();
        } elseif (!$data['is_published']) {
            $data['published_at'] = null;
        }

        // Re-slug only if title changed
        if ($data['title'] !== $post->title) {
            $data['slug'] = BlogPost::uniqueSlug($data['title']);
        }

        $post->update($data);

        return redirect()->route('admin.blog.index')->with('success', 'Post updated successfully.');
    }

    public function destroy(BlogPost $post)
    {
        $post->delete();
        return redirect()->route('admin.blog.index')->with('success', 'Post deleted.');
    }

    public function togglePublish(BlogPost $post)
    {
        $post->is_published = !$post->is_published;
        $post->published_at = $post->is_published ? ($post->published_at ?? now()) : null;
        $post->save();

        $status = $post->is_published ? 'published' : 'unpublished';
        return back()->with('success', "Post {$status}.");
    }
}
