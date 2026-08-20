<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AppointmentService
{
    public function tableExists(): bool
    {
        return Schema::hasTable('appointments');
    }

    public function ensureTableExists(): void
    {
        if ($this->tableExists()) {
            return;
        }

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('subscriber_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->text('remarks')->nullable();
            $table->enum('send_via', ['email', 'sms', 'both'])->default('email');
            $table->string('calendly_link')->nullable();
            $table->string('calendly_event_uri')->nullable();
            $table->enum('status', [
                'pending',
                'accepted',
                'canceled',
                'completed',
            ])->default('pending');
            $table->timestamps();
        });
    }

    public function resolveSubscriberId(User $user): int
    {
        return empty($user->added_by) ? (int) $user->id : (int) $user->added_by;
    }

    public function loadForSubscriber(int $subscriberId, int $limit = 100): Collection
    {
        if (!$this->tableExists()) {
            return collect();
        }

        $query = Appointment::where('subscriber_id', $subscriberId)
            ->whereNotNull('appointment_date')
            ->with(['client', 'user']);

        if (Schema::hasColumn('appointments', 'appointment_time')) {
            $query->orderByRaw('TIMESTAMP(appointment_date, appointment_time) DESC');
        } else {
            $query->orderByDesc('appointment_date');
        }

        return $query->orderByDesc('created_at')->limit($limit)->get();
    }
}
