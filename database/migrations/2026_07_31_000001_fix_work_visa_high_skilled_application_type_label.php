<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_LABEL = 'Work Visa - High Skilled';

    private const NEW_LABEL = 'Work Visa - Skilled';

    public function up(): void
    {
        $this->replaceExactLabel(self::OLD_LABEL, self::NEW_LABEL);
        $this->replaceEmbeddedLabel(self::OLD_LABEL, self::NEW_LABEL);
        $this->updateSubscriberCcSettings(self::OLD_LABEL, self::NEW_LABEL);
    }

    public function down(): void
    {
        $this->replaceExactLabel(self::NEW_LABEL, self::OLD_LABEL);
        $this->replaceEmbeddedLabel(self::NEW_LABEL, self::OLD_LABEL);
        $this->updateSubscriberCcSettings(self::NEW_LABEL, self::OLD_LABEL);
    }

    private function replaceExactLabel(string $from, string $to): void
    {
        $updates = [
            ['client_jobs', 'job'],
            ['applications', 'application_name'],
            ['services', 'service_name'],
        ];

        if (Schema::hasTable('visa_enquiries') && Schema::hasColumn('visa_enquiries', 'visa_category')) {
            $updates[] = ['visa_enquiries', 'visa_category'];
        }

        foreach ($updates as [$table, $column]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::table($table)
                ->where($column, $from)
                ->update([$column => $to]);
        }
    }

    private function replaceEmbeddedLabel(string $from, string $to): void
    {
        if (! Schema::hasTable('services') || ! Schema::hasColumn('services', 'service_name')) {
            return;
        }

        DB::table('services')
            ->where('service_name', 'like', '%' . $from . '%')
            ->where('service_name', '!=', $from)
            ->update([
                'service_name' => DB::raw("REPLACE(service_name, '" . addslashes($from) . "', '" . addslashes($to) . "')"),
            ]);
    }

    private function updateSubscriberCcSettings(string $from, string $to): void
    {
        if (! Schema::hasTable('subscriber_cc_settings')) {
            return;
        }

        $rows = DB::table('subscriber_cc_settings')->get(['id', 'visa_categories', 'document_lists']);

        foreach ($rows as $row) {
            $payload = [];

            if (Schema::hasColumn('subscriber_cc_settings', 'visa_categories')) {
                $categories = $this->decodeJsonArray($row->visa_categories);
                if ($categories !== null) {
                    $updated = $this->replaceInList($categories, $from, $to);
                    if ($updated !== $categories) {
                        $payload['visa_categories'] = json_encode(array_values($updated));
                    }
                }
            }

            if (Schema::hasColumn('subscriber_cc_settings', 'document_lists')) {
                $lists = $this->decodeJsonArray($row->document_lists);
                if ($lists !== null) {
                    $updated = false;
                    foreach ($lists as &$entry) {
                        if (! is_array($entry)) {
                            continue;
                        }

                        if (($entry['visa_category'] ?? null) === $from) {
                            $entry['visa_category'] = $to;
                            $updated = true;
                        }
                    }
                    unset($entry);

                    if ($updated) {
                        $payload['document_lists'] = json_encode(array_values($lists));
                    }
                }
            }

            if ($payload !== []) {
                DB::table('subscriber_cc_settings')
                    ->where('id', $row->id)
                    ->update($payload);
            }
        }
    }

    private function decodeJsonArray(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function replaceInList(array $values, string $from, string $to): array
    {
        return array_map(static fn ($value) => $value === $from ? $to : $value, $values);
    }
};
