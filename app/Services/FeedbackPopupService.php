<?php

namespace App\Services;

use App\Models\Feedbacks;
use App\Models\User;
use Carbon\Carbon;

class FeedbackPopupService
{
    public function isEligibleUser(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $type = strtolower((string) $user->user_type);

        return in_array($type, ['subscriber', 'user'], true);
    }

    public function anchorDate(User $user): ?Carbon
    {
        if (strtolower((string) $user->user_type) === 'subscriber' && !empty($user->membership_start_date)) {
            return Carbon::parse($user->membership_start_date)->startOfDay();
        }

        if (!empty($user->created_at)) {
            return Carbon::parse($user->created_at)->startOfDay();
        }

        return null;
    }

    public function shouldShowPopup(User $user): bool
    {
        if (!$this->isEligibleUser($user)) {
            return false;
        }

        $anchor = $this->anchorDate($user);
        if (!$anchor) {
            return false;
        }

        $now = Carbon::now()->startOfDay();
        $firstEligibleDate = $anchor->copy()->addDays(90);

        if ($now->lt($firstEligibleDate)) {
            return false;
        }

        $completedYears = (int) $anchor->diffInYears($now);
        $yearStart = $anchor->copy()->addYears($completedYears);
        $yearEnd = $anchor->copy()->addYears($completedYears + 1);
        $windowStart = $completedYears === 0 ? $firstEligibleDate : $yearStart;

        return !Feedbacks::where('user_id', $user->id)
            ->where('created_at', '>=', $windowStart)
            ->where('created_at', '<', $yearEnd)
            ->exists();
    }
}
