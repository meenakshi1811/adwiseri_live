<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sign-up "Sub-Category" list:
 *  - "General (All Countries)" reads "General (All Countries) Visa Consultant".
 *  - Adds the practice types shown under Use Cases on the homepage that the list was missing:
 *    work-visa specialists and PR / settlement advisories.
 *
 * Sub-category names are stored as text on subscribers, their applications and the job roles
 * lookup, so a rename updates every one of those (same as the UK OISC label migrations).
 */
class UpdateGeneralAndAddWorkPrSubscriberSubcategories extends Migration
{
    private const OLD_GENERAL = 'General (All Countries)';
    private const NEW_GENERAL = 'General (All Countries) Visa Consultant';

    /** Added under the same category as the General entry. */
    private const NEW_SUB_CATEGORIES = [
        'Business / Work Visa Specialists',
        'PR / Settlement / Immigration Advisories',
    ];

    public function up()
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

    public function down()
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

            DB::table($table)
                ->where($column, $from)
                ->update([$column => $to]);
        }
    }
}
