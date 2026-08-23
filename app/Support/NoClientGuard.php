<?php

namespace App\Support;

use App\Models\Clients;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class NoClientGuard
{
    public static function hasClients(User $user): bool
    {
        if ($user->user_type === 'Subscriber') {
            return Clients::where('subscriber_id', $user->id)->exists();
        }

        return Clients::where('user_id', $user->id)->exists();
    }

    public static function redirectIfNoClients(User $user): ?RedirectResponse
    {
        if (!self::hasClients($user)) {
            return back()->with('noclient', true);
        }

        return null;
    }
}
