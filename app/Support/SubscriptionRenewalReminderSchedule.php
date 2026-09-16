<?php

namespace App\Support;

class SubscriptionRenewalReminderSchedule
{
    public const PRE_EXPIRY_DAYS = [60, 30, 14, 7, 5, 2, 0];

    public const POST_EXPIRY_WEEKLY_DAYS = [7, 14, 21, 28];

    public const POST_EXPIRY_FINAL_DAY = 30;
}
