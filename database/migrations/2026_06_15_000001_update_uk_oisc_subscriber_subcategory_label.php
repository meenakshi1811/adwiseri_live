<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateUkOiscSubscriberSubcategoryLabel extends Migration
{
    private const OLD_LABEL = 'UK - OISC/Immigration Solicitor';
    private const NEW_LABEL = 'UK - OISC (IAA) Immigration Advisor';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->replaceLabel(self::OLD_LABEL, self::NEW_LABEL);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->replaceLabel(self::NEW_LABEL, self::OLD_LABEL);
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
