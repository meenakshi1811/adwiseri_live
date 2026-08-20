<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToEmailSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('email_subscriptions', 'email')) {
                $table->string('email')->unique()->after('id');
            }
            if (!Schema::hasColumn('email_subscriptions', 'status')) {
                $table->string('status', 20)->default('subscribed')->after('email');
            }
            if (!Schema::hasColumn('email_subscriptions', 'unsubscribed_at')) {
                $table->timestamp('unsubscribed_at')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('email_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('email_subscriptions', 'unsubscribed_at')) {
                $table->dropColumn('unsubscribed_at');
            }
            if (Schema::hasColumn('email_subscriptions', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
}
