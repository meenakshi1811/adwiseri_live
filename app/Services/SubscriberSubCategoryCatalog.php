<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Keeps the sign-up "Sub-Category" list current:
 *  - "General (All Countries)" reads "General (All Countries) Visa Consultant" (renamed everywhere the
 *    name is stored: the list itself, subscribers, applications and the job-roles lookup);
 *  - the Use Cases practice types that were missing are added: work-visa specialists and
 *    PR / settlement advisories.
 *
 * Runs from the 2026_11_22 migration and, so the site does not depend on that migration having been
 * run on the server, once from the sub-category dropdown (remembered in the cache afterwards).
 */
class SubscriberSubCategoryCatalog
{
    public const OLD_GENERAL = 'General (All Countries)';
    public const NEW_GENERAL = 'General (All Countries) Visa Consultant';

    /** Added under the same category as the General entry. */
    public const NEW_SUB_CATEGORIES = [
        'Business / Work Visa Specialists',
        'PR / Settlement / Immigration Advisories',
    ];

    private const CACHE_KEY = 'subscriber_subcategories_catalog_20261122';

    /** Applies the update at most once (per cache lifetime); never breaks the page if it fails. */
    public function ensureCurrent(): void
    {
        if (Cache::get(self::CACHE_KEY)) {
            return;
        }

        try {
            $this->apply();
            Cache::forever(self::CACHE_KEY, true);
        } catch (\Throwable $e) {
            Log::warning('Sub-category list update skipped: ' . $e->getMessage());
        }
    }

    public function apply(): void
    {
        $this->replaceLabel(self::OLD_GENERAL, self::NEW_GENERAL);

        if (!Schema::hasTable('subscriber_sub_categories')) {
            return;
        }

        // The General row is the template: same category, status and any other columns.
        $template = DB::table('subscriber_sub_categories')
            ->where('sub_category_name', self::NEW_GENERAL)
            ->orderBy('id')
            ->first();

        if (!$template) {
            return;
        }

        $base = (array) $template;
        unset($base['id']);
        $now = now();

        foreach (self::NEW_SUB_CATEGORIES as $name) {
            $exists = DB::table('subscriber_sub_categories')
                ->where('category_name', $template->category_name)
                ->where('sub_category_name', $name)
                ->exists();

            if ($exists) {
                continue;
            }

            $row = array_merge($base, ['sub_category_name' => $name]);
            if (array_key_exists('status', $row)) {
                $row['status'] = 'Active';
            }
            if (array_key_exists('created_at', $row)) {
                $row['created_at'] = $now;
            }
            if (array_key_exists('updated_at', $row)) {
                $row['updated_at'] = $now;
            }

            DB::table('subscriber_sub_categories')->insert($row);
        }
    }

    public function revert(): void
    {
        if (Schema::hasTable('subscriber_sub_categories')) {
            foreach (self::NEW_SUB_CATEGORIES as $name) {
                // Keep a sub-category that subscribers have already chosen.
                $inUse = Schema::hasColumn('users', 'sub_category')
                    && DB::table('users')->where('sub_category', $name)->exists();

                if (!$inUse) {
                    DB::table('subscriber_sub_categories')->where('sub_category_name', $name)->delete();
                }
            }
        }

        $this->replaceLabel(self::NEW_GENERAL, self::OLD_GENERAL);
        Cache::forget(self::CACHE_KEY);
    }

    private function replaceLabel(string $from, string $to): void
    {
        $updates = [
            ['subscriber_sub_categories', 'sub_category_name'],
            ['users', 'sub_category'],
            ['applications', 'application_subcategory'],
            ['client_jobs', 'sub_category'],
        ];

        foreach ($updates as [$table, $column]) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::table($table)->where($column, $from)->update([$column => $to]);
        }
    }
}
