<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\ForumCategory;
use App\Models\ForumReaction;
use App\Models\ForumReply;
use App\Models\ForumReport;
use App\Models\ForumTopic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ForumController extends Controller
{
    public function index(Request $request): View
    {
        $category = null;
        if ($request->filled('category')) {
            $category = ForumCategory::where('slug', (string) $request->query('category'))->where('is_active', true)->firstOrFail();
        }

        $search = trim((string) $request->query('q', ''));
        $sort = $request->query('sort') === 'trending' ? 'trending' : 'recent';
        $topics = ForumTopic::published()
            ->with(['user', 'category', 'blogPost'])
            ->withCount(['replies as visible_replies_count' => fn ($query) => $query->where('status', 'published'), 'reactions'])
            ->when($category, fn ($query) => $query->where('category_id', $category->id))
            ->when($search !== '', fn ($query) => $query->where(fn ($inner) => $inner->where('title', 'like', '%' . $search . '%')->orWhere('body', 'like', '%' . $search . '%')))
            ->orderByDesc('is_pinned')
            ->when($sort === 'trending', fn ($query) => $query->orderByDesc('replies_count')->orderByDesc('views_count'))
            ->when($sort === 'recent', fn ($query) => $query->orderByDesc('last_activity_at'))
            ->paginate(15)
            ->withQueryString();

        $categories = ForumCategory::where('is_active', true)->withCount(['topics as visible_topics_count' => fn ($query) => $query->where('status', 'published')])->orderBy('sort_order')->get();
        $stats = [
            'members' => DB::table('forum_topics')->distinct('user_id')->count('user_id'),
            'topics' => ForumTopic::published()->count(),
            'replies' => ForumReply::where('status', 'published')->count(),
        ];

        return view('forum.index', compact('topics', 'categories', 'category', 'search', 'sort', 'stats'));
    }

    public function create(Request $request): View
    {
        $categories = ForumCategory::where('is_active', true)->orderBy('sort_order')->get();
        $blogPost = $request->filled('blog') ? BlogPost::published()->where('slug', $request->query('blog'))->firstOrFail() : null;
        $selectedCategoryId = $request->filled('category')
            ? ForumCategory::whereKey($request->integer('category'))->where('is_active', true)->value('id')
            : null;

        return view('forum.create', compact('categories', 'blogPost', 'selectedCategoryId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:forum_categories,id'],
            'title' => ['required', 'string', 'min:8', 'max:180'],
            'body' => ['required', 'string', 'min:20', 'max:12000'],
            'blog_post_id' => ['nullable', 'integer', 'exists:blog_posts,id'],
        ]);

        abort_unless(ForumCategory::whereKey($data['category_id'])->where('is_active', true)->exists(), 422, 'Choose an active discussion category.');
        if (!empty($data['blog_post_id'])) {
            abort_unless(BlogPost::published()->whereKey($data['blog_post_id'])->exists(), 422, 'That article is no longer available.');
        }

        $slug = Str::slug($data['title']) . '-' . Str::lower(Str::random(7));
        $topic = ForumTopic::create([
            ...$data,
            'user_id' => $request->user()->id,
            'slug' => $slug,
            'status' => 'published',
            'last_activity_at' => now(),
        ]);

        return redirect()->route('forum.show', $topic->slug)->with('success', 'Your discussion is live. Please keep it helpful and respectful.');
    }

    public function show(Request $request, string $slug): View
    {
        $topic = ForumTopic::published()
            ->where('slug', $slug)
            ->with(['user', 'category', 'blogPost'])
            ->withCount(['reactions', 'replies as visible_replies_count' => fn ($query) => $query->where('status', 'published')])
            ->firstOrFail();

        $viewKey = 'forum_topic_viewed_' . $topic->id;
        if (!$request->session()->has($viewKey)) {
            $topic->increment('views_count');
            $request->session()->put($viewKey, true);
        }

        $replies = $topic->replies()
            ->where('status', 'published')
            ->with('user:id,name,kx_tag,is_admin')
            ->withCount('reactions')
            ->orderBy('created_at')
            ->paginate(30);

        $userReacted = $request->user()
            ? ForumReaction::where('user_id', $request->user()->id)->where('topic_id', $topic->id)->exists()
            : false;
        $reactedReplies = $request->user()
            ? ForumReaction::where('user_id', $request->user()->id)->whereIn('reply_id', $replies->pluck('id'))->pluck('reply_id')->all()
            : [];

        return view('forum.show', compact('topic', 'replies', 'userReacted', 'reactedReplies'));
    }

    public function reply(Request $request, ForumTopic $topic): RedirectResponse
    {
        abort_unless($topic->status === 'published', 404);
        if ($topic->is_locked) return back()->with('error', 'This discussion is closed to new replies.');

        $data = $request->validate(['body' => ['required', 'string', 'min:2', 'max:8000']]);
        DB::transaction(function () use ($request, $topic, $data) {
            ForumReply::create(['topic_id' => $topic->id, 'user_id' => $request->user()->id, 'body' => $data['body'], 'status' => 'published']);
            $topic->increment('replies_count');
            $topic->forceFill(['last_activity_at' => now()])->save();
        });

        return redirect()->route('forum.show', $topic->slug . '#replies')->with('success', 'Your reply has been posted.');
    }

    public function react(Request $request, ForumTopic $topic): RedirectResponse
    {
        abort_unless($topic->status === 'published', 404);
        $existing = ForumReaction::where('user_id', $request->user()->id)->where('topic_id', $topic->id)->first();
        $existing ? $existing->delete() : ForumReaction::create(['user_id' => $request->user()->id, 'topic_id' => $topic->id, 'reaction' => 'helpful']);

        return back()->with('status', $existing ? 'Reaction removed.' : 'Marked as helpful.');
    }

    public function reactToReply(Request $request, ForumReply $reply): RedirectResponse
    {
        abort_unless($reply->status === 'published' && $reply->topic?->status === 'published', 404);
        $existing = ForumReaction::where('user_id', $request->user()->id)->where('reply_id', $reply->id)->first();
        $existing ? $existing->delete() : ForumReaction::create(['user_id' => $request->user()->id, 'reply_id' => $reply->id, 'reaction' => 'helpful']);

        return back()->with('status', $existing ? 'Reaction removed.' : 'Marked as helpful.');
    }

    public function report(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'topic_id' => ['nullable', 'required_without:reply_id', 'prohibits:reply_id', 'integer', 'exists:forum_topics,id'],
            'reply_id' => ['nullable', 'required_without:topic_id', 'prohibits:topic_id', 'integer', 'exists:forum_replies,id'],
            'reason' => ['required', 'in:scam,spam,abuse,misinformation,other'],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);
        if (!empty($data['reply_id'])) {
            $reply = ForumReply::with('topic')->findOrFail($data['reply_id']);
            abort_unless($reply->topic?->status === 'published', 404);
        } elseif (!ForumTopic::published()->whereKey($data['topic_id'])->exists()) {
            abort(404);
        }

        $duplicate = ForumReport::where('reporter_id', $request->user()->id)
            ->where('status', 'open')
            ->when(!empty($data['topic_id']), fn ($query) => $query->where('topic_id', $data['topic_id']))
            ->when(!empty($data['reply_id']), fn ($query) => $query->where('reply_id', $data['reply_id']))
            ->exists();
        if (!$duplicate) {
            ForumReport::create([...$data, 'reporter_id' => $request->user()->id, 'status' => 'open']);
        }

        return back()->with('status', $duplicate ? 'You already reported this item. Our moderators will review it.' : 'Report sent to the moderation team.');
    }
}
