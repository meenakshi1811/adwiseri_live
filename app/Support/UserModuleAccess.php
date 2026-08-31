<?php

namespace App\Support;

use App\Models\User;
use App\Models\UserRoles;

class UserModuleAccess
{
    public static function isPrivileged(User $user): bool
    {
        return in_array($user->user_type, ['Subscriber', 'admin'], true);
    }

    public static function canRead(?UserRoles $role, User $user): bool
    {
        if (self::isPrivileged($user)) {
            return true;
        }

        if (!$role) {
            return false;
        }

        return (int) $role->read_only === 1
            || (int) $role->read_write_only === 1
            || (int) $role->write_only === 1
            || (int) $role->update_only === 1;
    }

    public static function canWrite(?UserRoles $role, User $user): bool
    {
        if (self::isPrivileged($user)) {
            return true;
        }

        if (!$role) {
            return false;
        }

        return (int) $role->write_only === 1 || (int) $role->read_write_only === 1;
    }

    public static function canUpdate(?UserRoles $role, User $user): bool
    {
        if (self::isPrivileged($user)) {
            return true;
        }

        if (!$role) {
            return false;
        }

        return (int) $role->update_only === 1 || (int) $role->read_write_only === 1;
    }

    public static function canDelete(?UserRoles $role, User $user): bool
    {
        if (self::isPrivileged($user)) {
            return true;
        }

        if (!$role) {
            return false;
        }

        return (int) $role->delete_only === 1 || (int) $role->read_write_only === 1;
    }
}
