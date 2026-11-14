<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForumCategory;
use App\Models\ForumReply;
use App\Models\ForumReport;
use App\Models\ForumTopic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminForumController extends Controller
{
    public function index(): View
    {
        $topics = ForumTopic::with(['user:id,name,email,is_admin', 'category:id,name'])
            ->withCount(['replies as visible_replies_count' => fn ($query) => $query->where('status', 'published')])
            ->latest('last_activity_at')->paginate(15, ['*'], 'topics_page');

        $reports = ForumReport::with(['reporter:id,name,email', 'topic:id,title,slug', 'reply:id,topic_id,body', 'reply.topic:id,title,slug'])
            ->latest()->paginate(15, ['*'], 'reports_page');

        $categories = ForumCategory::withCount('topics')->orderBy('sort_order')->get();
        $activeCategories = $categories->where('is_active', true);
        $stats = [
            'topics' => ForumTopic::count(),
            'replies' => ForumReply::count(),
            'open_reports' => ForumReport::where('status', 'open')->count(),
            'hidden' => ForumTopic::where('status', 'hidden')->count() + ForumReply::where('status', 'hidden')->count(),
        ];

        return view('admin.forum.index', compact('topics', 'reports', 'categories', 'activeCategories', 'stats'));
    }

    public function storeOfficialTopic(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:forum_categories,id'],
            'title' => ['required', 'string', 'min:8', 'max:180'],
            'body' => ['required', 'string', 'min:20', 'max:12000'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);
        abort_unless(ForumCategory::whereKey($data['category_id'])->where('is_active', true)->exists(), 422, 'Choose an active forum category.');

        $topic = ForumTopic::create([
            'user_id' => $request->user()->id,
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'body' => $data['body'],
            'slug' => Str::slug($data['title']) . '-' . Str::lower(Str::random(7)),
            'status' => 'published',
            'is_pinned' => $request->boolean('is_pinned'),
            'last_activity_at' => now(),
        ]);

        return redirect()->route('admin.forum.index')->with('success', 'Official KayXchange community post published.');
    }

    public function replyOfficial(Request $request, ForumTopic $topic): RedirectResponse
    {
        abort_unless($topic->status === 'published', 404);
        if ($topic->is_locked) return back()->withErrors(['official_reply' => 'This discussion is closed to new replies.']);

        $data = $request->validate(['body' => ['required', 'string', 'min:2', 'max:8000']]);
        DB::transaction(function () use ($request, $topic, $data): void {
            $lockedTopic = ForumTopic::query()->whereKey($topic->id)->lockForUpdate()->firstOrFail();
            if ($lockedTopic->is_locked || $lockedTopic->status !== 'published') {
                throw \Illuminate\Validation\ValidationException::withMessages(['official_reply' => 'This discussion was closed before the reply could be posted.']);
            }
            ForumReply::create([
                'topic_id' => $topic->id,
                'user_id' => $request->user()->id,
                'body' => $data['body'],
                'status' => 'published',
            ]);
            $topic->increment('replies_count');
            $topic->forceFill(['last_activity_at' => now()])->save();
        });

        return back()->with('success', 'Official KayXchange reply published.');
    }

    public function updateTopic(Request $request, ForumTopic $topic): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:published,hidden'],
            'title' => ['nullable', 'string', 'min:8', 'max:180'],
            'body' => ['nullable', 'string', 'min:20', 'max:12000'],
            'is_pinned' => ['nullable', 'boolean'],
            'is_locked' => ['nullable', 'boolean'],
        ]);

        $topic->update([
            'status' => $data['status'],
            'title' => $data['title'] ?? $topic->title,
            'body' => $data['body'] ?? $topic->body,
            'is_pinned' => $request->boolean('is_pinned'),
            'is_locked' => $request->boolean('is_locked'),
        ]);

        return back()->with('success', 'Discussion moderation settings updated.');
    }

    public function resolveReport(Request $request, ForumReport $report): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:dismiss,hide'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $report, $data) {
            if ($data['decision'] === 'hide') {
                if ($report->reply_id) {
                    $reply = ForumReply::lockForUpdate()->find($report->reply_id);
                    if ($reply && $reply->status === 'published') {
                        $reply->update(['status' => 'hidden']);
                        $reply->topic()->decrement('replies_count');
                    }
                } elseif ($report->topic_id) {
                    ForumTopic::whereKey($report->topic_id)->update(['status' => 'hidden']);
                }
            }

            $report->update([
                'status' => $data['decision'] === 'hide' ? 'actioned' : 'reviewed',
                'reviewed_by' => $request->user()->id,
                'admin_note' => $data['admin_note'] ?? null,
                'reviewed_at' => now(),
            ]);
        });

        return back()->with('success', $data['decision'] === 'hide' ? 'Reported content hidden and report actioned.' : 'Report reviewed and dismissed.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:forum_categories,name'],
            'description' => ['nullable', 'string', 'max:220'],
            'icon' => ['required', 'string', 'max:60', 'regex:/^bi-[a-z0-9-]+$/'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $slug = Str::slug($data['name']);
        if (ForumCategory::where('slug', $slug)->exists()) $slug .= '-' . Str::lower(Str::random(4));
        ForumCategory::create([...$data, 'slug' => $slug, 'is_active' => true, 'sort_order' => (int) ForumCategory::max('sort_order') + 1]);

        return back()->with('success', 'Forum category created.');
    }

    public function updateCategory(Request $request, ForumCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:forum_categories,name,' . $category->id],
            'description' => ['nullable', 'string', 'max:220'],
            'icon' => ['required', 'string', 'max:60', 'regex:/^bi-[a-z0-9-]+$/'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:1000'],
        ]);

        $slug = Str::slug($data['name']);
        if (ForumCategory::where('slug', $slug)->where('id', '!=', $category->id)->exists()) $slug .= '-' . Str::lower(Str::random(4));
        $category->update([...$data, 'slug' => $slug, 'is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'Forum category settings saved.');
    }
}
