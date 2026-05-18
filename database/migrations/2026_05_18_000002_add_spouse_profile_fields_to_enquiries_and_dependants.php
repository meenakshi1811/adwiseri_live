<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpouseProfileFieldsToEnquiriesAndDependants extends Migration
{
    public function up()
    {
        if (Schema::hasTable('visa_enquiries')) {
            Schema::table('visa_enquiries', function (Blueprint $table) {
                if (!Schema::hasColumn('visa_enquiries', 'spouse_age')) {
                    $table->unsignedSmallInteger('spouse_age')->nullable()->after('spouse_name');
                }

                if (!Schema::hasColumn('visa_enquiries', 'spouse_qualification')) {
                    $table->string('spouse_qualification')->nullable()->after('spouse_age');
                }

                if (!Schema::hasColumn('visa_enquiries', 'spouse_work_experience_years')) {
                    $table->decimal('spouse_work_experience_years', 5, 2)->nullable()->after('spouse_qualification');
                }
            });
        }

        if (Schema::hasTable('dependants')) {
            Schema::table('dependants', function (Blueprint $table) {
                if (!Schema::hasColumn('dependants', 'age')) {
                    $table->unsignedSmallInteger('age')->nullable()->after('dob');
                }

                if (!Schema::hasColumn('dependants', 'qualification')) {
                    $table->string('qualification')->nullable()->after('gender');
                }

                if (!Schema::hasColumn('dependants', 'work_experience_years')) {
                    $table->decimal('work_experience_years', 5, 2)->nullable()->after('qualification');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('dependants')) {
            Schema::table('dependants', function (Blueprint $table) {
                if (Schema::hasColumn('dependants', 'work_experience_years')) {
                    $table->dropColumn('work_experience_years');
                }

                if (Schema::hasColumn('dependants', 'qualification')) {
                    $table->dropColumn('qualification');
                }

                if (Schema::hasColumn('dependants', 'age')) {
                    $table->dropColumn('age');
                }
            });
        }

        if (Schema::hasTable('visa_enquiries')) {
            Schema::table('visa_enquiries', function (Blueprint $table) {
                if (Schema::hasColumn('visa_enquiries', 'spouse_work_experience_years')) {
                    $table->dropColumn('spouse_work_experience_years');
                }

                if (Schema::hasColumn('visa_enquiries', 'spouse_qualification')) {
                    $table->dropColumn('spouse_qualification');
                }

                if (Schema::hasColumn('visa_enquiries', 'spouse_age')) {
                    $table->dropColumn('spouse_age');
                }
            });
        }
    }
}
