<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnquiryFormSettingsService
{
    public const SECTION_KEYS = [
        'default',
        'q1',
        'q2',
        'q3',
        'q4',
        'q5',
        'q6',
        'q7',
        'q8',
        'q9',
        'q10',
        'signature_box',
    ];

    public const SECTION_LABELS = [
        'default' => 'Default — Full Enquiry Form (All Questions + Signature Box)',
        'q1' => 'Q1 — Abroad Residency History',
        'q2' => 'Q2 — Travel History',
        'q3' => 'Q3 — Visa Refusal History',
        'q4' => 'Q4 — Highest Qualification',
        'q5' => 'Q5 — English Language Proficiency',
        'q6' => 'Q6 — Work Experience',
        'q7' => 'Q7 — Spouse Personal Details',
        'q8' => 'Q8 — Children',
        'q9' => 'Q9 — Study Funding Source',
        'q10' => 'Q10 — Consent, Privacy & Terms',
        'signature_box' => 'Signature Box',
    ];

    public function sectionOptions(): array
    {
        $options = [];

        foreach (self::SECTION_LABELS as $key => $label) {
            $options[$key] = ['label' => $label];
        }

        return $options;
    }

    public function defaultSections(): array
    {
        $defaults = [];

        foreach (self::SECTION_KEYS as $key) {
            $defaults[$key] = true;
        }

        return $defaults;
    }

    public function settingsTableExists(): bool
    {
        return Schema::hasTable('subscriber_enquiry_form_settings');
    }

    public function ensureSettingsTableExists(): void
    {
        if ($this->settingsTableExists()) {
            return;
        }

        Schema::create('subscriber_enquiry_form_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscriber_id')->unique();
            $table->json('sections')->nullable();
            $table->timestamps();

            $table->foreign('subscriber_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function getSetting(User $subscriber): ?object
    {
        if (!$this->settingsTableExists()) {
            return null;
        }

        $row = DB::table('subscriber_enquiry_form_settings')
            ->where('subscriber_id', $subscriber->id)
            ->first();

        if (!$row) {
            return null;
        }

        return $this->hydrateSettingRow($row);
    }

    public function hasSavedSettings(User $subscriber): bool
    {
        $setting = $this->getSetting($subscriber);
        $sections = $setting->sections ?? null;

        return is_array($sections) && count($sections) > 0;
    }

    public function resolveSections(User $subscriber): array
    {
        $setting = $this->getSetting($subscriber);
        $saved = ($setting && is_array($setting->sections) && count($setting->sections) > 0)
            ? $setting->sections
            : $this->defaultSections();

        return $this->normalizeSections($saved);
    }

    public function resolveSectionsForSubscriberId(int $subscriberId): array
    {
        $subscriber = User::find($subscriberId);

        if (!$subscriber) {
            return $this->defaultSections();
        }

        return $this->resolveSections($subscriber);
    }

    public function isEnabled(array $sections, string $key): bool
    {
        if (!array_key_exists($key, $sections)) {
            return false;
        }

        return $this->toBoolean($sections[$key]);
    }

    public function saveSettings(User $subscriber, array $sections): object
    {
        $this->ensureSettingsTableExists();

        $payload = [
            'sections' => json_encode($this->normalizeSections($sections)),
            'updated_at' => now(),
        ];

        $existingId = DB::table('subscriber_enquiry_form_settings')
            ->where('subscriber_id', $subscriber->id)
            ->value('id');

        if ($existingId) {
            DB::table('subscriber_enquiry_form_settings')
                ->where('id', $existingId)
                ->update($payload);
        } else {
            DB::table('subscriber_enquiry_form_settings')->insert(array_merge($payload, [
                'subscriber_id' => $subscriber->id,
                'created_at' => now(),
            ]));
        }

        return $this->getSetting($subscriber) ?? (object) [
            'subscriber_id' => $subscriber->id,
            'sections' => $this->normalizeSections($sections),
        ];
    }

    public function resetToDefaults(User $subscriber): object
    {
        return $this->saveSettings($subscriber, $this->defaultSections());
    }

    public function normalizeSections(array $sections): array
    {
        $normalized = $this->defaultSections();

        foreach (self::SECTION_KEYS as $key) {
            if (array_key_exists($key, $sections)) {
                $normalized[$key] = $this->toBoolean($sections[$key]);
            }
        }

        $normalized['default'] = true;

        return $normalized;
    }

    public function isFullFormEnabled(array $sections): bool
    {
        $normalized = $this->normalizeSections($sections);

        foreach (self::SECTION_KEYS as $key) {
            if (empty($normalized[$key])) {
                return false;
            }
        }

        return true;
    }

    private function hydrateSettingRow(object $row): object
    {
        $sections = $row->sections ?? null;

        if (is_string($sections)) {
            $decoded = json_decode($sections, true);
            $sections = is_array($decoded) ? $decoded : null;
        }

        $row->sections = is_array($sections) ? $sections : null;

        return $row;
    }

    private function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
