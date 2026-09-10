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
            if (!Schema::hasColumn('subscriber_cc_settings', 'auto_send_document_checklist')) {
                $table->boolean('auto_send_document_checklist')->default(false)->after('document_lists');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('subscriber_cc_settings')) {
            return;
        }

        Schema::table('subscriber_cc_settings', function (Blueprint $table) {
            if (Schema::hasColumn('subscriber_cc_settings', 'auto_send_document_checklist')) {
                $table->dropColumn('auto_send_document_checklist');
            }
        });
    }
};
