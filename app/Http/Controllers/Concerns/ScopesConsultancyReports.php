<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;

trait ScopesConsultancyReports
{
    protected function hasConsultancyReportAccess(User $user): bool
    {
        return in_array($user->membership, ['Adwiseri', 'Adwiseri+', 'Enterprise'], true)
            && in_array($user->user_type, ['Subscriber', 'User'], true);
    }

    protected function isConsultancyMember(User $user): bool
    {
        return in_array($user->user_type, ['Subscriber', 'User'], true);
    }

    protected function consultancySubscriberId(User $user): ?int
    {
        if ($user->user_type === 'Subscriber') {
            return (int) $user->id;
        }

        if ($user->user_type === 'User' && $user->added_by) {
            return (int) $user->added_by;
        }

        return null;
    }

    protected function scopeQueryToConsultancy($query, User $user, string $column = 'subscriber_id')
    {
        $subscriberId = $this->consultancySubscriberId($user);

        if ($subscriberId) {
            return $query->where($column, $subscriberId);
        }

        return $query;
    }
}
