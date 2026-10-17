<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('internal_communications')) {
            return;
        }

        if (!Schema::hasColumn('internal_communications', 'attachments')) {
            Schema::table('internal_communications', function (Blueprint $table) {
                $table->json('attachments')->nullable()->after('message');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('internal_communications') && Schema::hasColumn('internal_communications', 'attachments')) {
            Schema::table('internal_communications', function (Blueprint $table) {
                $table->dropColumn('attachments');
            });
        }
    }
};
