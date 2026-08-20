<?php

namespace App\Console\Commands;

use App\Mail\AppointmentSchedulerMail;
use App\Models\Appointment;
use App\Models\Clients;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendTestAppointmentEmail extends Command
{
    protected $signature = 'appointment:send-test
                            {appointment : Appointment ID}
                            {--to= : Override recipient email for testing}';

    protected $description = 'Send a test appointment invitation email for an existing appointment';

    public function handle(): int
    {
        $appointment = Appointment::find($this->argument('appointment'));
        if (!$appointment) {
            $this->error('Appointment not found.');

            return self::FAILURE;
        }

        $client = Clients::find($appointment->client_id);
        $sender = User::find($appointment->user_id);
        if (!$client || !$sender) {
            $this->error('Appointment is missing client or sender details.');

            return self::FAILURE;
        }

        $recipient = trim((string) ($this->option('to') ?: $client->email));
        if ($recipient === '') {
            $this->error('No recipient email available. Pass --to=email@example.com');

            return self::FAILURE;
        }

        $scheduledAt = $appointment->scheduledAt(config('app.timezone'));
        $expiresAt = ($scheduledAt ?? now())->copy()->addDay();

        $routeParams = ['appointment' => $appointment->id, 'email' => $recipient];
        $appointment->setAttribute('accept_url', URL::temporarySignedRoute(
            'appointment.respond',
            $expiresAt,
            array_merge($routeParams, ['action' => 'accept'])
        ));
        $appointment->setAttribute('decline_url', URL::temporarySignedRoute(
            'appointment.respond',
            $expiresAt,
            array_merge($routeParams, ['action' => 'decline'])
        ));

        $this->line('Mailer : ' . config('mail.default'));
        $this->line('From   : ' . config('mail.from.address'));
        $this->line('To     : ' . $recipient);
        $this->newLine();

        try {
            Mail::to($recipient)->send(new AppointmentSchedulerMail($appointment, $client, $sender));
            $this->info('Appointment invitation email sent successfully.');
            $this->line('Accept URL: ' . $appointment->accept_url);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to send email: ' . $e->getMessage());
            $this->line('Tip: set MAIL_MAILER=log in .env to capture emails in storage/logs/laravel.log while testing locally.');

            return self::FAILURE;
        }
    }
}
