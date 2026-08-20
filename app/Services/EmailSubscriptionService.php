<?php

namespace App\Services;

use App\Models\EmailSubscriptions;
use Illuminate\Support\Facades\Schema;

class EmailSubscriptionService
{
    public function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    public function subscribe(string $email): EmailSubscriptions
    {
        if (!Schema::hasColumn('email_subscriptions', 'email')) {
            throw new \RuntimeException('Email subscriptions table is missing the email column. Please run database migrations.');
        }

        $email = $this->normalizeEmail($email);

        $existing = EmailSubscriptions::query()
            ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
            ->first();

        if ($existing) {
            $existing->status = EmailSubscriptions::STATUS_SUBSCRIBED;
            $existing->unsubscribed_at = null;
            $existing->save();

            return $existing;
        }

        return EmailSubscriptions::create([
            'email' => $email,
            'status' => EmailSubscriptions::STATUS_SUBSCRIBED,
        ]);
    }
}
