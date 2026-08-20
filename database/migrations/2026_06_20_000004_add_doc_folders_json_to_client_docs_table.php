<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('client_docs', 'doc_folders')) {
            Schema::table('client_docs', function (Blueprint $table) {
                $table->json('doc_folders')->nullable()->after('doc_folder');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('client_docs', 'doc_folders')) {
            Schema::table('client_docs', function (Blueprint $table) {
                $table->dropColumn('doc_folders');
            });
        }
    }
};
