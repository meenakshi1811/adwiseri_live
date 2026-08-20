<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internal_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('user_id');
            $table->string('created_by_name')->nullable()->after('created_by');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by_name');
            $table->string('updated_by_name')->nullable()->after('updated_by');
        });

        DB::table('internal_invoices')
            ->whereNotNull('user_id')
            ->orderBy('id')
            ->chunkById(200, function ($invoices) {
                foreach ($invoices as $invoice) {
                    $user = DB::table('users')->where('id', $invoice->user_id)->first();
                    if (!$user) {
                        continue;
                    }

                    DB::table('internal_invoices')
                        ->where('id', $invoice->id)
                        ->update([
                            'created_by' => $user->id,
                            'created_by_name' => $user->name . ' (' . $user->email . ')',
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('internal_invoices', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'created_by_name', 'updated_by', 'updated_by_name']);
        });
    }
};
