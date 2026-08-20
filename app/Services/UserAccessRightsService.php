<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class UserAccessRightsService
{
    public const MODULES = [
        'Dashboard',
        'Clients',
        'Applications',
        'Communication',
        'Associates',
        'Invoices',
        'Payments',
        'Reports',
        'Subscription',
        'Settings',
        'Support',
    ];

    public const MODULE_LABELS = [
        'Associates' => 'Associates (B2B)',
    ];

    public function labelForModule(string $module): string
    {
        return self::MODULE_LABELS[$module] ?? $module;
    }

    public const PRESET_KEYS = [
        'director_manager',
        'counsellor_advisor',
        'sales_support',
        'accountant',
    ];

    public function presetOptions(): array
    {
        return [
            'full_access' => [
                'label' => 'Full Access (Subscriber Level) Rights',
                'description' => 'All modules + all operational rights',
            ],
            'director_manager' => [
                'label' => 'Director / Manager / Associate Solicitor',
                'description' => 'Same as Full Access (Subscriber Level) Rights, without Delete operations.',
            ],
            'counsellor_advisor' => [
                'label' => 'Counsellor / Advisor',
                'description' => 'All modules except Dashboard, Users, Referrals, Wallets & Settings. Read, Write, Insert & Update access.',
            ],
            'sales_support' => [
                'label' => 'Sales / Support',
                'description' => 'All modules except Dashboard, Users, Referrals, Wallets & Settings. Read, Insert & Update access.',
            ],
            'accountant' => [
                'label' => 'Accountant',
                'description' => 'All modules except Dashboard, Clients, Users, Applications, Referrals, Wallets & Settings. Read, Insert & Update access.',
            ],
            'limited_access' => [
                'label' => 'Customised Access',
                'description' => 'Set modules and access rights manually',
            ],
        ];
    }

    public function labelForAccessType(string $accessType): string
    {
        $options = $this->presetOptions();

        return $options[$accessType]['label'] ?? 'Customised Access';
    }

    public function accessTypeForDesignation(?string $designation): string
    {
        $value = Str::lower(trim((string) $designation));

        if ($value === '' || $value === 'other') {
            return 'sales_support';
        }

        // Exact / specific designations first (order matters).
        if ($value === 'solicitor partner' || Str::contains($value, 'solicitor partner')) {
            return 'full_access';
        }

        if ($value === 'associate solicitor' || Str::contains($value, 'associate solicitor')) {
            return 'director_manager';
        }

        if (Str::contains($value, 'para-legal') || Str::contains($value, 'paralegal')) {
            return 'counsellor_advisor';
        }

        $fullAccessKeywords = [
            'director',
            'branch manager',
            'operations manager',
            'principal solicitor',
        ];
        foreach ($fullAccessKeywords as $keyword) {
            if (Str::contains($value, $keyword)) {
                return 'full_access';
            }
        }

        $counsellorKeywords = [
            'counsellor',
            'counselor',
            'advisor',
            'consultant',
            'lawyer',
            'solicitor',
        ];
        foreach ($counsellorKeywords as $keyword) {
            if (Str::contains($value, $keyword)) {
                return 'counsellor_advisor';
            }
        }

        $accountantKeywords = [
            'accountant',
            'accounts',
            'hr',
            'human resource',
        ];
        foreach ($accountantKeywords as $keyword) {
            if (Str::contains($value, $keyword)) {
                return 'accountant';
            }
        }

        $salesSupportKeywords = [
            'sales',
            'support',
        ];
        foreach ($salesSupportKeywords as $keyword) {
            if (Str::contains($value, $keyword)) {
                return 'sales_support';
            }
        }

        return 'sales_support';
    }

    public function applyDefaultAccessRights(User $staff, int $subscriberId): void
    {
        UserRoles::where('user_id', $staff->id)->delete();

        $accessType = $this->accessTypeForDesignation($staff->designation);
        $this->saveFromPermissions($staff, $subscriberId, $this->permissionsForAccessType($accessType));
    }

    public function presetPermissionsForJs(): array
    {
        $permissions = [
            'full_access' => $this->permissionsForAccessType('full_access'),
        ];

        foreach (self::PRESET_KEYS as $presetKey) {
            $permissions[$presetKey] = $this->permissionsForAccessType($presetKey);
        }

        return $permissions;
    }

    public function detectAccessType(Collection $roles): string
    {
        if ($roles->isEmpty()) {
            return 'full_access';
        }

        $rolesByModule = $roles->keyBy('module');

        if ($this->matchesPresetPermissions($rolesByModule, $this->fullAccessPermissions())) {
            return 'full_access';
        }

        if ($this->matchesPresetPermissions($rolesByModule, $this->directorManagerPermissions())) {
            return 'director_manager';
        }

        foreach (self::PRESET_KEYS as $presetKey) {
            if ($presetKey === 'director_manager') {
                continue;
            }
            if ($this->matchesPresetPermissions($rolesByModule, $this->presetPermissions($presetKey))) {
                return $presetKey;
            }
        }

        return 'limited_access';
    }

    public function resolveAccessTypeForUser(User $staff, Collection $roles): string
    {
        if ($roles->isEmpty()) {
            return $this->accessTypeForDesignation($staff->designation);
        }

        return $this->detectAccessType($roles);
    }

    public function buildMatrixFromRoles(Collection $savedRoles): array
    {
        $saved = $savedRoles->keyBy('module');
        $permissions = [];

        foreach (self::MODULES as $module) {
            $role = $saved->get($module);
            $permissions[$module] = $role ? [
                'read_only' => (int) $role->read_only,
                'write_only' => (int) $role->write_only,
                'update_only' => (int) $role->update_only,
                'delete_only' => (int) $role->delete_only,
                'read_write_only' => (int) $role->read_write_only,
            ] : $this->noAccess();
        }

        return $this->formatMatrix($permissions);
    }

    public function buildMatrixFromAccessType(string $accessType, ?Collection $savedRoles = null): array
    {
        if ($accessType === 'limited_access' && $savedRoles && $savedRoles->isNotEmpty()) {
            return $this->buildMatrixFromRoles($savedRoles);
        }

        return $this->formatMatrix($this->permissionsForAccessType($accessType));
    }

    public function userHasDashboardAccess(User $user): bool
    {
        if (in_array($user->user_type, ['Subscriber', 'admin'], true)) {
            return true;
        }

        $dashboardRole = UserRoles::where('user_id', $user->id)
            ->where('module', 'Dashboard')
            ->first();

        return $dashboardRole && ((int) $dashboardRole->read_only === 1 || (int) $dashboardRole->read_write_only === 1);
    }

    public function saveAccessRights(User $staff, int $subscriberId, string $accessType, Request $request): void
    {
        if ($accessType === 'full_access') {
            $this->saveFromPermissions($staff, $subscriberId, $this->fullAccessPermissions());
            return;
        }

        if (in_array($accessType, self::PRESET_KEYS, true)) {
            $this->saveFromPermissions($staff, $subscriberId, $this->presetPermissions($accessType));
            return;
        }

        if ($accessType === 'limited_access') {
            $this->saveLimitedAccessFromRequest($staff, $subscriberId, $request);
        }
    }

    public function permissionsForAccessType(string $accessType): array
    {
        if ($accessType === 'full_access') {
            return $this->fullAccessPermissions();
        }

        if (in_array($accessType, self::PRESET_KEYS, true)) {
            return $this->presetPermissions($accessType);
        }

        return $this->defaultNewUserLimitedPermissions();
    }

    private function saveFromPermissions(User $staff, int $subscriberId, array $permissionsByModule): void
    {
        foreach (self::MODULES as $module) {
            $permissions = $permissionsByModule[$module] ?? $this->noAccess();

            $role = new UserRoles();
            $role->user_id = $staff->id;
            $role->subscriber_id = $subscriberId;
            $role->name = $staff->name;
            $role->email = $staff->email;
            $role->module = $module;
            $role->read_only = $permissions['read_only'];
            $role->write_only = $permissions['write_only'];
            $role->update_only = $permissions['update_only'];
            $role->delete_only = $permissions['delete_only'];
            $role->read_write_only = $permissions['read_write_only'];
            $role->save();
        }
    }

    private function saveLimitedAccessFromRequest(User $staff, int $subscriberId, Request $request): void
    {
        $permissionsByModule = [];

        foreach (self::MODULES as $module) {
            $prefix = strtolower($module) . '_';
            $permissionsByModule[$module] = [
                'read_only' => ($request->input($prefix . 'read_only') == 1) ? 1 : 0,
                'write_only' => ($request->input($prefix . 'write_only') == 1) ? 1 : 0,
                'update_only' => ($request->input($prefix . 'update_only') == 1) ? 1 : 0,
                'delete_only' => ($request->input($prefix . 'delete_only') == 1) ? 1 : 0,
                'read_write_only' => ($request->input($prefix . 'read_write_only') == 1) ? 1 : 0,
            ];
        }

        $this->saveFromPermissions($staff, $subscriberId, $permissionsByModule);
    }

    private function formatMatrix(array $permissions): array
    {
        $matrix = [];

        foreach (self::MODULES as $module) {
            $matrix[] = array_merge(
                [
                    'module' => $module,
                    'module_label' => $this->labelForModule($module),
                ],
                $permissions[$module] ?? $this->noAccess()
            );
        }

        return $matrix;
    }

    private function fullAccessPermissions(): array
    {
        $permissions = [];
        $fullAccess = $this->allOperationalRights();

        foreach (self::MODULES as $module) {
            $permissions[$module] = $fullAccess;
        }

        return $permissions;
    }

    private function directorManagerPermissions(): array
    {
        $permissions = [];
        $rights = $this->allOperationalRightsWithoutDelete();

        foreach (self::MODULES as $module) {
            $permissions[$module] = $rights;
        }

        return $permissions;
    }

    private function defaultNewUserLimitedPermissions(): array
    {
        $permissions = [];

        foreach (self::MODULES as $module) {
            $permissions[$module] = $this->noAccess();
        }

        $fullAccess = $this->allOperationalRights();
        foreach (['Clients', 'Applications', 'Communication', 'Associates', 'Invoices', 'Payments', 'Support'] as $module) {
            $permissions[$module] = $fullAccess;
        }

        return $permissions;
    }

    private function presetPermissions(string $presetKey): array
    {
        if ($presetKey === 'director_manager') {
            return $this->directorManagerPermissions();
        }

        $permissions = [];

        foreach (self::MODULES as $module) {
            $permissions[$module] = $this->noAccess();
        }

        if ($presetKey === 'counsellor_advisor') {
            $rights = $this->counsellorRights();
            foreach (['Clients', 'Applications', 'Communication', 'Associates', 'Invoices', 'Payments', 'Reports', 'Subscription', 'Support'] as $module) {
                $permissions[$module] = $rights;
            }
        }

        if ($presetKey === 'sales_support') {
            $rights = $this->readInsertUpdateRights();
            foreach (['Clients', 'Applications', 'Communication', 'Associates', 'Invoices', 'Payments', 'Reports', 'Subscription', 'Support'] as $module) {
                $permissions[$module] = $rights;
            }
        }

        if ($presetKey === 'accountant') {
            $rights = $this->readInsertUpdateRights();
            foreach (['Communication', 'Invoices', 'Payments', 'Reports', 'Subscription', 'Support'] as $module) {
                $permissions[$module] = $rights;
            }
            // Dashboard, Clients, Applications, Settings intentionally remain no-access.
        }

        return $permissions;
    }

    private function matchesPresetPermissions(Collection $rolesByModule, array $expectedPermissions): bool
    {
        foreach (self::MODULES as $module) {
            $role = $rolesByModule->get($module);
            $expected = $expectedPermissions[$module] ?? $this->noAccess();

            if (!$role) {
                if ($expected !== $this->noAccess()) {
                    return false;
                }
                continue;
            }

            foreach (['read_only', 'write_only', 'update_only', 'delete_only', 'read_write_only'] as $field) {
                if ((int) $role->{$field} !== (int) $expected[$field]) {
                    return false;
                }
            }
        }

        return true;
    }

    private function noAccess(): array
    {
        return [
            'read_only' => 0,
            'write_only' => 0,
            'update_only' => 0,
            'delete_only' => 0,
            'read_write_only' => 0,
        ];
    }

    private function allOperationalRights(): array
    {
        return [
            'read_only' => 1,
            'write_only' => 1,
            'update_only' => 1,
            'delete_only' => 1,
            'read_write_only' => 1,
        ];
    }

    private function allOperationalRightsWithoutDelete(): array
    {
        return [
            'read_only' => 1,
            'write_only' => 1,
            'update_only' => 1,
            'delete_only' => 0,
            'read_write_only' => 1,
        ];
    }

    private function counsellorRights(): array
    {
        return [
            'read_only' => 1,
            'write_only' => 1,
            'update_only' => 1,
            'delete_only' => 0,
            'read_write_only' => 1,
        ];
    }

    private function readInsertUpdateRights(): array
    {
        return [
            'read_only' => 1,
            'write_only' => 1,
            'update_only' => 1,
            'delete_only' => 0,
            'read_write_only' => 0,
        ];
    }
}
