<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('client_accounts')) {
            return;
        }

        Schema::table('client_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('client_accounts', 'payment_mode')) {
                $table->string('payment_mode', 100)->nullable()->after('amount');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('client_accounts')) {
            return;
        }

        Schema::table('client_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('client_accounts', 'payment_mode')) {
                $table->dropColumn('payment_mode');
            }
        });
    }
};
