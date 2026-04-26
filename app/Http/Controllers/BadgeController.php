<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BadgeController extends Controller
{
    public function __construct(private BadgeService $badgeService) {}

    /**
     * User badges page — shows all badges, earned/unearned, with pin controls.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $allBadges = Badge::orderBy('sort_order')->get();
        $earnedIds = $user->badges()->pluck('badges.id')->toArray();

        $pinnedBadges = $user->pinnedBadges()->with('badge')->get();

        // Group badges by category for display
        $grouped = $allBadges->groupBy('category');

        return view('badges.index', compact('user', 'allBadges', 'earnedIds', 'pinnedBadges', 'grouped'));
    }

    /**
     * Pin a badge (AJAX).
     */
    public function pin(Badge $badge)
    {
        $result = $this->badgeService->pin(Auth::user(), $badge->id);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Unpin a badge (AJAX).
     */
    public function unpin(Badge $badge)
    {
        $result = $this->badgeService->unpin(Auth::user(), $badge->id);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Admin: award a badge to a user.
     */
    public function adminAward(Request $request, User $user)
    {
        $request->validate([
            'badge_id' => 'required|exists:badges,id',
        ]);

        $badge = Badge::findOrFail($request->badge_id);
        $this->badgeService->adminAward($user, $badge, Auth::user());

        return back()->with('success', "Badge \"{$badge->name}\" awarded to {$user->name}.");
    }

    /**
     * Admin: revoke a badge from a user.
     */
    public function adminRevoke(User $user, Badge $badge)
    {
        $this->badgeService->adminRevoke($user, $badge);

        return back()->with('success', "Badge \"{$badge->name}\" revoked from {$user->name}.");
    }
}
