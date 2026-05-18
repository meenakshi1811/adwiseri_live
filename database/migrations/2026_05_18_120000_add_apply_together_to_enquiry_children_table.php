<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiry_children', function (Blueprint $table) {
            if (!Schema::hasColumn('enquiry_children', 'apply_together')) {
                $table->boolean('apply_together')->default(0)->after('child_dob');
            }
        });
    }

    public function down(): void
    {
        Schema::table('enquiry_children', function (Blueprint $table) {
            if (Schema::hasColumn('enquiry_children', 'apply_together')) {
                $table->dropColumn('apply_together');
            }
        });
    }
};
