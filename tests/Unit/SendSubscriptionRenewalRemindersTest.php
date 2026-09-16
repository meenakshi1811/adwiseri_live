<?php

namespace Tests\Unit;

use App\Support\SubscriptionRenewalReminderSchedule;
use PHPUnit\Framework\TestCase;

class SendSubscriptionRenewalRemindersTest extends TestCase
{
    public function test_post_expiry_weekly_reminders_stop_with_final_day_30(): void
    {
        $this->assertSame([7, 14, 21, 28], SubscriptionRenewalReminderSchedule::POST_EXPIRY_WEEKLY_DAYS);
        $this->assertSame(30, SubscriptionRenewalReminderSchedule::POST_EXPIRY_FINAL_DAY);
        $this->assertLessThan(
            SubscriptionRenewalReminderSchedule::POST_EXPIRY_FINAL_DAY,
            max(SubscriptionRenewalReminderSchedule::POST_EXPIRY_WEEKLY_DAYS)
        );
    }

    public function test_pre_expiry_schedule_is_unchanged(): void
    {
        $this->assertSame([60, 30, 14, 7, 5, 2, 0], SubscriptionRenewalReminderSchedule::PRE_EXPIRY_DAYS);
    }
}
