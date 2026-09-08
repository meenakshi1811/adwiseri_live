<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('applications')) {
            return;
        }

        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications', 'document_checklist_upload_token')) {
                $table->string('document_checklist_upload_token', 64)->nullable()->after('document_checklist_sent_to');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('applications')) {
            return;
        }

        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'document_checklist_upload_token')) {
                $table->dropColumn('document_checklist_upload_token');
            }
        });
    }
};
