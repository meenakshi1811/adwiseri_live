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

    /**
     * Scope internal_invoices to the logged-in consultancy.
     * Always use subscriber_id — user_id stores the creator, not the tenant.
     */
    protected function scopeInternalInvoicesToConsultancy($query, User $user)
    {
        if (!$this->hasConsultancyReportAccess($user)) {
            return $query;
        }

        $subscriberId = $this->consultancySubscriberId($user);
        if ($subscriberId) {
            return $query->where('subscriber_id', $subscriberId);
        }

        return $query;
    }
}
