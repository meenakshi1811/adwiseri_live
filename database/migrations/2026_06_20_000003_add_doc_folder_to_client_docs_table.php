<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('client_docs', 'doc_folder')) {
            Schema::table('client_docs', function (Blueprint $table) {
                $table->string('doc_folder', 120)->nullable()->after('doc_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('client_docs', 'doc_folder')) {
            Schema::table('client_docs', function (Blueprint $table) {
                $table->dropColumn('doc_folder');
            });
        }
    }
};
