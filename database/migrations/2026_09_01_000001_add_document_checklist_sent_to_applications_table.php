<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications', 'document_checklist_sent_at')) {
                $table->timestamp('document_checklist_sent_at')->nullable()->after('updated_at');
            }
            if (!Schema::hasColumn('applications', 'document_checklist_sent_to')) {
                $table->string('document_checklist_sent_to', 255)->nullable()->after('document_checklist_sent_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'document_checklist_sent_to')) {
                $table->dropColumn('document_checklist_sent_to');
            }
            if (Schema::hasColumn('applications', 'document_checklist_sent_at')) {
                $table->dropColumn('document_checklist_sent_at');
            }
        });
    }
};
