<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionRenewalReminder;
use App\Models\User;
use App\Support\EmailAddress;
use App\Support\SubscriptionRenewalReminderSchedule;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSubscriptionRenewalReminders extends Command
{
    protected $signature = 'subscriptions:send-reminders';

    protected $description = 'Send subscription renewal reminders before expiry and weekly win-back reminders after expiry';

    public function handle()
    {
        Log::info('Subscription reminder command started.');

        $today = Carbon::today();

        $this->sendPreExpiryReminders($today);
        $this->sendPostExpiryReminders($today);

        Log::info('Subscription reminder command finished.');

        return self::SUCCESS;
    }

    private function sendPreExpiryReminders(Carbon $today): void
    {
        foreach (SubscriptionRenewalReminderSchedule::PRE_EXPIRY_DAYS as $daysRemaining) {
            $targetDate = $today->copy()->addDays($daysRemaining);
            $subscribers = $this->subscribersExpiringOn($targetDate);

            if ($subscribers->isEmpty()) {
                Log::info("No subscribers found for reminder of {$daysRemaining} days remaining.");
                continue;
            }

            foreach ($subscribers as $subscriber) {
                $this->sendReminder($subscriber, $daysRemaining, SubscriptionRenewalReminder::PHASE_PRE_EXPIRY);
            }
        }
    }

    private function sendPostExpiryReminders(Carbon $today): void
    {
        foreach (SubscriptionRenewalReminderSchedule::POST_EXPIRY_WEEKLY_DAYS as $daysExpired) {
            $this->sendPostExpiryForDay(
                $today,
                $daysExpired,
                SubscriptionRenewalReminder::PHASE_WINBACK
            );
        }

        $this->sendPostExpiryForDay(
            $today,
            SubscriptionRenewalReminderSchedule::POST_EXPIRY_FINAL_DAY,
            SubscriptionRenewalReminder::PHASE_WINBACK_FINAL
        );
    }

    private function sendPostExpiryForDay(Carbon $today, int $daysExpired, string $phase): void
    {
        $targetDate = $today->copy()->subDays($daysExpired);
        $subscribers = $this->subscribersExpiringOn($targetDate);

        if ($subscribers->isEmpty()) {
            Log::info("No subscribers found for post-expiry reminder of {$daysExpired} days.");
            return;
        }

        foreach ($subscribers as $subscriber) {
            $this->sendReminder($subscriber, $daysExpired, $phase);
        }
    }

    private function subscribersExpiringOn(Carbon $targetDate)
    {
        return User::where('user_type', 'Subscriber')
            ->whereDate('membership_expiry_date', $targetDate->toDateString())
            ->get();
    }

    private function sendReminder($subscriber, int $days, string $phase): void
    {
        if (!EmailAddress::isValidRecipient($subscriber->email ?? null)) {
            Log::warning('Skipping subscription reminder; invalid email.', [
                'subscriber_id' => $subscriber->id,
                'phase' => $phase,
            ]);
            return;
        }

        try {
            Mail::to($subscriber->email)->send(new SubscriptionRenewalReminder($subscriber, $days, $phase));
            $this->info("Reminder sent to {$subscriber->email} ({$phase}, {$days} days).");
            Log::info('Subscription reminder sent.', [
                'subscriber_id' => $subscriber->id,
                'email' => $subscriber->email,
                'phase' => $phase,
                'days' => $days,
            ]);
        } catch (\Throwable $e) {
            $this->error("Failed to send reminder to {$subscriber->email}: {$e->getMessage()}");
            Log::warning('Subscription reminder failed.', [
                'subscriber_id' => $subscriber->id,
                'email' => $subscriber->email,
                'phase' => $phase,
                'days' => $days,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
