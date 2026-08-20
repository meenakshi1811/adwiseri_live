<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('applications')) {
            return;
        }

        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications', 'course_name')) {
                $table->string('course_name', 255)->default('NA')->after('application_program');
            }
            if (!Schema::hasColumn('applications', 'course_duration')) {
                $table->string('course_duration', 255)->default('NA')->after('course_name');
            }
            if (!Schema::hasColumn('applications', 'institution')) {
                $table->string('institution', 255)->default('NA')->after('course_duration');
            }
            if (!Schema::hasColumn('applications', 'admission_number')) {
                $table->string('admission_number', 255)->default('NA')->after('institution');
            }
            if (!Schema::hasColumn('applications', 'employer_name')) {
                $table->string('employer_name', 255)->default('NA')->after('admission_number');
            }
            if (!Schema::hasColumn('applications', 'employment_role')) {
                $table->string('employment_role', 255)->default('NA')->after('employer_name');
            }
            if (!Schema::hasColumn('applications', 'permit_duration')) {
                $table->string('permit_duration', 255)->default('NA')->after('employment_role');
            }
            if (!Schema::hasColumn('applications', 'sponsor_number')) {
                $table->string('sponsor_number', 255)->default('NA')->after('permit_duration');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('applications')) {
            return;
        }

        Schema::table('applications', function (Blueprint $table) {
            $columns = [
                'course_name',
                'course_duration',
                'institution',
                'admission_number',
                'employer_name',
                'employment_role',
                'permit_duration',
                'sponsor_number',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('applications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
