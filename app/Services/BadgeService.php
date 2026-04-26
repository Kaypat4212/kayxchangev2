<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\BuyTrade;
use App\Models\SellTrade;
use App\Models\User;
use App\Models\UserBadge;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BadgeService
{
    private const COMPLETED_STATUSES = ['completed', 'approved', 'successful'];

    /**
     * Check and award all eligible badges for a user after a given event.
     *
     * Events: 'genesis' | 'pin_set' | 'bank_added' | 'kyc_verified' | 'trade_completed'
     */
    public function checkAndAward(User $user, string $event): array
    {
        $awarded = [];

        try {
            $candidates = Badge::where('is_special', false)
                ->where('criteria_type', $event)
                ->orderBy('sort_order')
                ->get();

            foreach ($candidates as $badge) {
                /** @var Badge $badge */
                if ($this->alreadyHas($user, $badge->id)) continue;
                if ($this->meetsThreshold($user, $badge)) {
                    $this->award($user, $badge);
                    $awarded[] = $badge;
                }
            }

            // Trade events also trigger volume checks
            if ($event === 'trade_completed') {
                $volBadges = Badge::where('is_special', false)
                    ->where('criteria_type', 'trade_volume_ngn')
                    ->orderBy('sort_order')
                    ->get();

                foreach ($volBadges as $badge) {
                    /** @var Badge $badge */
                    if ($this->alreadyHas($user, $badge->id)) continue;
                    if ($this->meetsThreshold($user, $badge)) {
                        $this->award($user, $badge);
                        $awarded[] = $badge;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('BadgeService::checkAndAward failed', [
                'user_id' => $user->id,
                'event'   => $event,
                'error'   => $e->getMessage(),
            ]);
        }

        return $awarded;
    }

    /**
     * Admin manually awards a special (or any) badge to a user.
     */
    public function adminAward(User $user, Badge $badge, User $admin): UserBadge
    {
        $existing = UserBadge::where('user_id', $user->id)
            ->where('badge_id', $badge->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return UserBadge::create([
            'user_id'    => $user->id,
            'badge_id'   => $badge->id,
            'awarded_by' => $admin->id,
            'awarded_at' => now(),
        ]);
    }

    /**
     * Admin revokes a badge from a user.
     */
    public function adminRevoke(User $user, Badge $badge): void
    {
        UserBadge::where('user_id', $user->id)
            ->where('badge_id', $badge->id)
            ->delete();
    }

    /**
     * Pin a badge to the user's profile (max 3 slots).
     * Returns ['success' => bool, 'message' => string].
     */
    public function pin(User $user, int $badgeId): array
    {
        $ub = UserBadge::where('user_id', $user->id)
            ->where('badge_id', $badgeId)
            ->first();

        if (!$ub) {
            return ['success' => false, 'message' => 'You do not have this badge.'];
        }

        if ($ub->is_pinned) {
            return ['success' => false, 'message' => 'Badge is already pinned.'];
        }

        $pinned = UserBadge::where('user_id', $user->id)
            ->where('is_pinned', true)
            ->count();

        if ($pinned >= 3) {
            return ['success' => false, 'message' => 'You can only pin up to 3 badges. Unpin one first.'];
        }

        // Find next available slot
        $usedPositions = UserBadge::where('user_id', $user->id)
            ->where('is_pinned', true)
            ->pluck('pin_position')
            ->toArray();

        $position = collect([1, 2, 3])->first(fn($p) => !in_array($p, $usedPositions));

        $ub->update(['is_pinned' => true, 'pin_position' => $position]);

        return ['success' => true, 'message' => 'Badge pinned successfully.'];
    }

    /**
     * Unpin a badge from the user's profile.
     */
    public function unpin(User $user, int $badgeId): array
    {
        $ub = UserBadge::where('user_id', $user->id)
            ->where('badge_id', $badgeId)
            ->first();

        if (!$ub || !$ub->is_pinned) {
            return ['success' => false, 'message' => 'Badge is not pinned.'];
        }

        $ub->update(['is_pinned' => false, 'pin_position' => null]);

        return ['success' => true, 'message' => 'Badge unpinned.'];
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function alreadyHas(User $user, int $badgeId): bool
    {
        return UserBadge::where('user_id', $user->id)
            ->where('badge_id', $badgeId)
            ->exists();
    }

    private function meetsThreshold(User $user, Badge $badge): bool
    {
        return match ($badge->criteria_type) {
            'genesis'         => true,
            'pin_set'         => !empty($user->transaction_pin),
            'bank_added'      => !empty($user->account_number),
            'kyc_verified'    => (bool) $user->kyc_verified,
            'trade_count'     => $this->completedTradeCount($user) >= $badge->criteria_value,
            'trade_volume_ngn'=> $this->completedTradeVolumeNgn($user) >= $badge->criteria_value,
            default           => false,
        };
    }

    private function completedTradeCount(User $user): int
    {
        $sell = SellTrade::where('user_id', $user->id)
            ->whereIn('status', self::COMPLETED_STATUSES)
            ->count();

        $buy = BuyTrade::where('user_id', $user->id)
            ->whereIn('status', self::COMPLETED_STATUSES)
            ->count();

        return $sell + $buy;
    }

    private function completedTradeVolumeNgn(User $user): float
    {
        $sell = SellTrade::where('user_id', $user->id)
            ->whereIn('status', self::COMPLETED_STATUSES)
            ->sum('naira_amount');

        $buy = BuyTrade::where('user_id', $user->id)
            ->whereIn('status', self::COMPLETED_STATUSES)
            ->sum('naira_amount');

        return (float)($sell + $buy);
    }

    private function award(User $user, Badge $badge): UserBadge
    {
        return UserBadge::create([
            'user_id'    => $user->id,
            'badge_id'   => $badge->id,
            'awarded_by' => null,
            'awarded_at' => now(),
        ]);
    }
}
