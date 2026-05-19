<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpouseApplyTogetherToVisaEnquiries extends Migration
{
    public function up()
    {
        if (Schema::hasTable('visa_enquiries') && !Schema::hasColumn('visa_enquiries', 'spouse_apply_together')) {
            Schema::table('visa_enquiries', function (Blueprint $table) {
                $table->boolean('spouse_apply_together')->default(false)->after('spouse_name');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('visa_enquiries') && Schema::hasColumn('visa_enquiries', 'spouse_apply_together')) {
            Schema::table('visa_enquiries', function (Blueprint $table) {
                $table->dropColumn('spouse_apply_together');
            });
        }
    }
}
