<?php

namespace App\Services;

use App\Models\Application_assignments;
use App\Models\Applications;
use App\Models\User;
use App\Models\UserRoles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class ApplicationVisibilityService
{
    public function __construct(
        private UserAccessRightsService $accessRightsService
    ) {
    }

    public function resolveSubscriber(User $user): ?User
    {
        if ($user->user_type === 'Subscriber') {
            return $user;
        }

        if ($user->user_type === 'admin') {
            return $user;
        }

        if ($user->user_type === 'User' && $user->added_by) {
            return User::find($user->added_by);
        }

        return null;
    }

    /**
     * Full Access and Director/Manager presets (UAR) see all subscriber applications.
     */
    public function hasSubscriberLevelApplicationsAccess(User $user): bool
    {
        if (in_array($user->user_type, ['Subscriber', 'admin'], true)) {
            return true;
        }

        if ($user->user_type !== 'User') {
            return false;
        }

        $roles = UserRoles::where('user_id', $user->id)->get();

        return in_array(
            $this->accessRightsService->resolveAccessTypeForUser($user, $roles),
            ['full_access', 'director_manager'],
            true
        );
    }

    public function queryForUser(User $user, ?User $subscriber = null): Builder
    {
        if ($user->user_type === 'admin') {
            return Applications::query();
        }

        $subscriber = $subscriber ?? $this->resolveSubscriber($user);

        if (!$subscriber) {
            return Applications::query()->whereRaw('1 = 0');
        }

        $query = Applications::query()->where('subscriber_id', $subscriber->id);

        if ($this->hasSubscriberLevelApplicationsAccess($user)) {
            return $query;
        }

        return $this->applyAssignedStaffScope($query, $user);
    }

    public function canViewApplication(User $user, Applications $application): bool
    {
        if ($user->user_type === 'admin') {
            return true;
        }

        $subscriber = $this->resolveSubscriber($user);

        if (!$subscriber || (int) $application->subscriber_id !== (int) $subscriber->id) {
            return false;
        }

        if ($this->hasSubscriberLevelApplicationsAccess($user)) {
            return true;
        }

        return $this->isAssignedToUser($application, $user);
    }

    /**
     * @return list<string>
     */
    public function visibleApplicationReferenceIds(User $user, ?User $subscriber = null): array
    {
        return $this->queryForUser($user, $subscriber)
            ->pluck('application_id')
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->values()
            ->all();
    }

    private function applyAssignedStaffScope(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $scoped) use ($user) {
            $scoped->where('assign_to', $user->id);

            if (Schema::hasTable('application_assignments')) {
                $scoped->orWhereIn('application_id', function ($sub) use ($user) {
                    $sub->select('application_id')
                        ->from('application_assignments')
                        ->where('user_id', $user->id)
                        ->whereNotNull('application_id');
                });
            }
        });
    }

    private function isAssignedToUser(Applications $application, User $user): bool
    {
        if ((int) $application->assign_to === (int) $user->id) {
            return true;
        }

        if (!Schema::hasTable('application_assignments') || empty($application->application_id)) {
            return false;
        }

        return Application_assignments::query()
            ->where('user_id', $user->id)
            ->where('application_id', $application->application_id)
            ->exists();
    }
}
