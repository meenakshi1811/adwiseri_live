<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('subscriber_cc_settings')) {
            return;
        }

        Schema::table('subscriber_cc_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriber_cc_settings', 'document_lists')) {
                $table->json('document_lists')->nullable()->after('visa_categories');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('subscriber_cc_settings')) {
            return;
        }

        Schema::table('subscriber_cc_settings', function (Blueprint $table) {
            if (Schema::hasColumn('subscriber_cc_settings', 'document_lists')) {
                $table->dropColumn('document_lists');
            }
        });
    }
};
