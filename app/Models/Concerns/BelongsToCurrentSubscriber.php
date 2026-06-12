<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToCurrentSubscriber
{
    protected static function bootBelongsToCurrentSubscriber(): void
    {
        static::addGlobalScope('current_subscriber', function (Builder $builder): void {
            $user = Auth::user();

            if (!$user || $user->user_type === 'admin') {
                return;
            }

            $subscriberId = $user->user_type === 'Subscriber'
                ? $user->id
                : $user->added_by;

            if ($subscriberId) {
                $builder->where($builder->getModel()->getTable() . '.subscriber_id', $subscriberId);
                return;
            }

            $builder->whereRaw('1 = 0');
        });
    }
}
