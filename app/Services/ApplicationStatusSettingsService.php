<?php

namespace App\Services;

use App\Models\SubscriberApplicationStatusSetting;
use App\Models\User;
use App\Support\ApplicationStatuses;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ApplicationStatusSettingsService
{
    public const DEFAULT_CATEGORY_KEY = '__default__';

    public function resolveSubscriber(User $user): User
    {
        return app(CountryCategorySettingsService::class)->resolveSubscriber($user);
    }

    public function defaultFlow(): array
    {
        return ApplicationStatuses::FLOW;
    }

    public function defaultEndDateRequired(): array
    {
        return ApplicationStatuses::END_DATE_REQUIRED;
    }

    public function normalizeCategoryKey(?string $visaCategory): ?string
    {
        $category = trim((string) $visaCategory);

        return $category === '' ? null : $category;
    }

    public function resolveFlow(int $subscriberId, ?string $visaCategory = null): array
    {
        $saved = $this->getSavedFlow($subscriberId, $visaCategory);
        if ($saved !== null) {
            return $saved;
        }

        if ($visaCategory !== null && $this->normalizeCategoryKey($visaCategory) !== null) {
            $defaultSaved = $this->getSavedFlow($subscriberId, null);
            if ($defaultSaved !== null) {
                return $defaultSaved;
            }
        }

        return $this->defaultFlow();
    }

    public function resolveEndDateRequired(int $subscriberId, ?string $visaCategory = null): array
    {
        $setting = $this->findSetting($subscriberId, $visaCategory);
        if ($setting && is_array($setting->end_date_required)) {
            return array_values(array_filter($setting->end_date_required, static fn ($status) => is_string($status) && trim($status) !== ''));
        }

        if ($visaCategory !== null && $this->normalizeCategoryKey($visaCategory) !== null) {
            $defaultSetting = $this->findSetting($subscriberId, null);
            if ($defaultSetting && is_array($defaultSetting->end_date_required)) {
                return array_values(array_filter($defaultSetting->end_date_required, static fn ($status) => is_string($status) && trim($status) !== ''));
            }
        }

        $flow = $this->resolveFlow($subscriberId, $visaCategory);

        return array_values(array_intersect($this->defaultEndDateRequired(), $flow));
    }

    public function resolveFlowsByCategory(int $subscriberId, Collection $visaCategories): array
    {
        $map = [
            self::DEFAULT_CATEGORY_KEY => $this->resolveFlow($subscriberId, null),
        ];

        foreach ($visaCategories as $category) {
            $key = $this->normalizeCategoryKey($category);
            if ($key === null) {
                continue;
            }

            $map[$key] = $this->resolveFlow($subscriberId, $key);
        }

        return $map;
    }

    public function resolveEndDateRequiredByCategory(int $subscriberId, Collection $visaCategories): array
    {
        $map = [
            self::DEFAULT_CATEGORY_KEY => $this->resolveEndDateRequired($subscriberId, null),
        ];

        foreach ($visaCategories as $category) {
            $key = $this->normalizeCategoryKey($category);
            if ($key === null) {
                continue;
            }

            $map[$key] = $this->resolveEndDateRequired($subscriberId, $key);
        }

        return $map;
    }

    public function getSettingsPayload(User $subscriber): array
    {
        $visaCategories = app(CountryCategorySettingsService::class)
            ->resolveVisaCategoryNames($subscriber)
            ->values();

        $saved = $this->loadSavedSettings((int) $subscriber->id);
        $defaultStatuses = $saved[self::DEFAULT_CATEGORY_KEY]['statuses'] ?? $this->defaultFlow();
        $defaultEndDateRequired = $saved[self::DEFAULT_CATEGORY_KEY]['end_date_required'] ?? $this->defaultEndDateRequired();

        $byCategory = [];
        foreach ($visaCategories as $category) {
            $key = $this->normalizeCategoryKey($category);
            if ($key === null) {
                continue;
            }

            $byCategory[$key] = [
                'statuses' => $saved[$key]['statuses'] ?? $defaultStatuses,
                'end_date_required' => $saved[$key]['end_date_required'] ?? $defaultEndDateRequired,
                'has_custom' => isset($saved[$key]),
            ];
        }

        return [
            'default' => [
                'statuses' => $defaultStatuses,
                'end_date_required' => $defaultEndDateRequired,
                'has_custom' => isset($saved[self::DEFAULT_CATEGORY_KEY]),
            ],
            'by_category' => $byCategory,
            'visa_categories' => $visaCategories->all(),
            'system_default_statuses' => $this->defaultFlow(),
            'system_default_end_date_required' => $this->defaultEndDateRequired(),
        ];
    }

    public function saveSettings(User $subscriber, ?string $visaCategory, array $statuses, array $endDateRequired = []): void
    {
        if (!$this->tableExists()) {
            return;
        }

        $statuses = $this->sanitizeStatuses($statuses);
        if ($statuses === []) {
            throw new \InvalidArgumentException('Please add at least one application status.');
        }

        $endDateRequired = array_values(array_intersect($endDateRequired, $statuses));
        $categoryKey = $this->normalizeCategoryKey($visaCategory);

        SubscriberApplicationStatusSetting::updateOrCreate(
            [
                'subscriber_id' => (int) $subscriber->id,
                'visa_category' => $categoryKey,
            ],
            [
                'statuses' => $statuses,
                'end_date_required' => $endDateRequired,
            ]
        );
    }

    public function resetSettings(User $subscriber, ?string $visaCategory = null): void
    {
        if (!$this->tableExists()) {
            return;
        }

        $categoryKey = $this->normalizeCategoryKey($visaCategory);

        SubscriberApplicationStatusSetting::where('subscriber_id', (int) $subscriber->id)
            ->when(
                $categoryKey === null,
                fn ($query) => $query->whereNull('visa_category'),
                fn ($query) => $query->where('visa_category', $categoryKey)
            )
            ->delete();
    }

    public function resetAllSettings(User $subscriber): void
    {
        if (!$this->tableExists()) {
            return;
        }

        SubscriberApplicationStatusSetting::where('subscriber_id', (int) $subscriber->id)->delete();
    }

    public function hasSavedSettings(User $subscriber): bool
    {
        if (!$this->tableExists()) {
            return false;
        }

        return SubscriberApplicationStatusSetting::where('subscriber_id', (int) $subscriber->id)->exists();
    }

    public function isValidStatus(int $subscriberId, ?string $visaCategory, string $status): bool
    {
        return in_array($status, $this->resolveFlow($subscriberId, $visaCategory), true);
    }

    public function flowForApplication(int $subscriberId, ?string $visaCategory, ?string $currentStatus): array
    {
        $flow = $this->resolveFlow($subscriberId, $visaCategory);
        $currentStatus = ApplicationStatuses::normalize($currentStatus);

        if ($currentStatus !== '' && !in_array($currentStatus, $flow, true)) {
            return array_merge([$currentStatus], $flow);
        }

        return $flow;
    }

    public function mergedFlowForSubscriber(int $subscriberId): array
    {
        if (!$this->tableExists()) {
            return $this->defaultFlow();
        }

        $merged = [];
        $settings = SubscriberApplicationStatusSetting::where('subscriber_id', $subscriberId)->get();

        foreach ($this->defaultFlow() as $status) {
            $merged[$status] = true;
        }

        foreach ($settings as $setting) {
            foreach ((array) $setting->statuses as $status) {
                if (is_string($status) && trim($status) !== '') {
                    $merged[trim($status)] = true;
                }
            }
        }

        $ordered = [];
        foreach ($this->defaultFlow() as $status) {
            if (isset($merged[$status])) {
                $ordered[] = $status;
                unset($merged[$status]);
            }
        }

        foreach (array_keys($merged) as $status) {
            $ordered[] = $status;
        }

        return $ordered;
    }

    private function getSavedFlow(int $subscriberId, ?string $visaCategory): ?array
    {
        $setting = $this->findSetting($subscriberId, $visaCategory);
        if (!$setting) {
            return null;
        }

        $statuses = $this->sanitizeStatuses((array) $setting->statuses);

        return $statuses === [] ? null : $statuses;
    }

    private function findSetting(int $subscriberId, ?string $visaCategory): ?SubscriberApplicationStatusSetting
    {
        if (!$this->tableExists()) {
            return null;
        }

        $categoryKey = $this->normalizeCategoryKey($visaCategory);

        return SubscriberApplicationStatusSetting::where('subscriber_id', $subscriberId)
            ->when(
                $categoryKey === null,
                fn ($query) => $query->whereNull('visa_category'),
                fn ($query) => $query->where('visa_category', $categoryKey)
            )
            ->first();
    }

    private function loadSavedSettings(int $subscriberId): array
    {
        if (!$this->tableExists()) {
            return [];
        }

        $settings = [];
        $rows = SubscriberApplicationStatusSetting::where('subscriber_id', $subscriberId)->get();

        foreach ($rows as $row) {
            $key = $row->visa_category === null ? self::DEFAULT_CATEGORY_KEY : (string) $row->visa_category;
            $settings[$key] = [
                'statuses' => $this->sanitizeStatuses((array) $row->statuses),
                'end_date_required' => array_values(array_filter((array) $row->end_date_required, static fn ($status) => is_string($status) && trim($status) !== '')),
            ];
        }

        return $settings;
    }

    private function sanitizeStatuses(array $statuses): array
    {
        $clean = [];

        foreach ($statuses as $status) {
            if (!is_string($status)) {
                continue;
            }

            $status = trim($status);
            if ($status === '') {
                continue;
            }

            if (!in_array($status, $clean, true)) {
                $clean[] = $status;
            }
        }

        return $clean;
    }

    private function tableExists(): bool
    {
        return Schema::hasTable('subscriber_application_status_settings');
    }
}
