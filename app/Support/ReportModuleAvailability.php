<?php

namespace App\Support;

use App\Models\User;

/**
 * @deprecated Use ModuleAvailability::reportModules() instead.
 */
class ReportModuleAvailability
{
    /**
     * @return array<string, bool>
     */
    public static function forSubscriber(int $subscriberId): array
    {
        return ModuleAvailability::subscriberReportModules($subscriberId);
    }

    /**
     * @return array<string, bool>
     */
    public static function forAdmin(): array
    {
        return ModuleAvailability::adminReportModules();
    }

    public static function resolveForUser(User $user): array
    {
        return ModuleAvailability::reportModules($user);
    }
}
