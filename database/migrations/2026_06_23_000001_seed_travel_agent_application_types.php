<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CATEGORY = 'Travel Agent';

    private const LEGACY_CATEGORIES = [
        'Travel Agency',
        'Travel Agent',
    ];

    private const APPLICATION_TYPES = [
        'Visit Visa - Leisure / Tourism',
        'Visit Visa - Business / Medical',
        'Transit Visa',
        'Passport (New)',
        'Passport (Renewal)',
        'TOC',
        'Appeal',
        'Other',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('client_jobs')) {
            return;
        }

        DB::table('client_jobs')
            ->whereIn('category', self::LEGACY_CATEGORIES)
            ->delete();

        $now = now();

        foreach (self::APPLICATION_TYPES as $job) {
            $row = [
                'category' => self::CATEGORY,
                'job' => $job,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (Schema::hasColumn('client_jobs', 'sub_category')) {
                $row['sub_category'] = null;
            }

            DB::table('client_jobs')->insert($row);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('client_jobs')) {
            return;
        }

        DB::table('client_jobs')
            ->where('category', self::CATEGORY)
            ->whereIn('job', self::APPLICATION_TYPES)
            ->delete();
    }
};
