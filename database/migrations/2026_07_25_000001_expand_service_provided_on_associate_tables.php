<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Legacy service_provided columns now store the same comma-separated multi-select as `services`. */
    private array $tables = ['associate_businesses', 'associate_invoices', 'associate_payments'];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'service_provided')) {
                continue;
            }

            // Was varchar(50); multi-select labels exceed that (e.g. all 5 services ≈ 67 chars).
            DB::statement("ALTER TABLE `{$tableName}` MODIFY `service_provided` TEXT NULL");
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'service_provided')) {
                continue;
            }

            DB::statement("ALTER TABLE `{$tableName}` MODIFY `service_provided` VARCHAR(50) NULL");
        }
    }
};
