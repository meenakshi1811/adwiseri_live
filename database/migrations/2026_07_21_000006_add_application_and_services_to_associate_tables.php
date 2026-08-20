<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Tables that gain application-link + multi-service columns. */
    private array $tables = ['associate_businesses', 'associate_invoices', 'associate_payments'];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'application_id')) {
                    $table->unsignedBigInteger('application_id')->nullable()->after('client_name');
                }
                if (!Schema::hasColumn($tableName, 'application_name')) {
                    $table->string('application_name', 255)->nullable()->after('application_id');
                }
                // Multi-select services (comma-separated): Student Admission,
                // Job Recruitment, Visa Processing, Finance, Other.
                if (!Schema::hasColumn($tableName, 'services')) {
                    $table->text('services')->nullable()->after('service_provided');
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach (['application_id', 'application_name', 'services'] as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
