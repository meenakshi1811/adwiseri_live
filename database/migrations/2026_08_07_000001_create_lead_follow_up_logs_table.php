<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_follow_up_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('enquiry_id');
            $table->unsignedBigInteger('subscriber_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('client_name', 255)->nullable();
            $table->text('description');
            $table->timestamp('logged_at')->useCurrent();
            $table->timestamps();

            $table->index(['enquiry_id', 'logged_at']);
            $table->index(['subscriber_id', 'logged_at']);
        });

        if (Schema::hasTable('visa_enquiries')) {
            $enquiries = DB::table('visa_enquiries')
                ->whereNotNull('lead_worked_at')
                ->get([
                    'id',
                    'subscriber_id',
                    'lead_worked_by_user_id',
                    'full_name',
                    'lead_source',
                    'lead_status',
                    'lead_worked_at',
                    'created_at',
                ]);

            foreach ($enquiries as $enquiry) {
                $description = sprintf(
                    'Initial follow-up recorded. Source: %s; Status: %s.',
                    $enquiry->lead_source ?: 'Walk-in',
                    $enquiry->lead_status ?: 'Open'
                );

                DB::table('lead_follow_up_logs')->insert([
                    'enquiry_id' => $enquiry->id,
                    'subscriber_id' => $enquiry->subscriber_id,
                    'user_id' => $enquiry->lead_worked_by_user_id,
                    'client_id' => null,
                    'client_name' => $enquiry->full_name,
                    'description' => $description,
                    'logged_at' => $enquiry->lead_worked_at ?: $enquiry->created_at,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_follow_up_logs');
    }
};
