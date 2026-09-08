<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixUkOiscSubscriberSubcategoryLabel extends Migration
{
    private const NEW_LABEL = 'UK - OISC (IAA) Advisors';

    private const OLD_LABELS = [
        'UK - OISC Advisors',
        'UK - OISC/Immigration Solicitor',
        'UK - OISC (IAA) Immigration Advisor',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (self::OLD_LABELS as $oldLabel) {
            $this->replaceLabel($oldLabel, self::NEW_LABEL);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->replaceLabel(self::NEW_LABEL, 'UK - OISC Advisors');
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
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::table($table)
                ->where($column, $from)
                ->update([$column => $to]);
        }
    }
}
