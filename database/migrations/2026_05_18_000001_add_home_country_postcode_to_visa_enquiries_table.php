<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHomeCountryPostcodeToVisaEnquiriesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('visa_enquiries')) {
            return;
        }

        Schema::table('visa_enquiries', function (Blueprint $table) {
            if (!Schema::hasColumn('visa_enquiries', 'postcode')) {
                $table->string('postcode', 50)->nullable()->after('address');
            }

            if (!Schema::hasColumn('visa_enquiries', 'country')) {
                $table->string('country')->nullable()->after('postcode');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('visa_enquiries')) {
            return;
        }

        Schema::table('visa_enquiries', function (Blueprint $table) {
            if (Schema::hasColumn('visa_enquiries', 'country')) {
                $table->dropColumn('country');
            }

            if (Schema::hasColumn('visa_enquiries', 'postcode')) {
                $table->dropColumn('postcode');
            }
        });
    }
}
