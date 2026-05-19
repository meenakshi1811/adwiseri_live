<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddToDateToEnquiryWorkExperience extends Migration
{
    public function up()
    {
        if (Schema::hasTable('enquiry_work_experience') && !Schema::hasColumn('enquiry_work_experience', 'to_date')) {
            Schema::table('enquiry_work_experience', function (Blueprint $table) {
                $table->date('to_date')->nullable()->after('joining_date');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('enquiry_work_experience') && Schema::hasColumn('enquiry_work_experience', 'to_date')) {
            Schema::table('enquiry_work_experience', function (Blueprint $table) {
                $table->dropColumn('to_date');
            });
        }
    }
}
