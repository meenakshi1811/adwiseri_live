<?php

namespace App\Mail;

use App\Models\Appointment;
use App\Models\Clients;
use App\Models\User;
use App\Support\BrandedMail;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentResponseMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment,
        public Clients $client,
        public User $sender,
        public string $response
    ) {
    }

    public function build()
    {
        $accepted = $this->response === 'accepted';
        $seekNext = $this->response === 'seek_next';
        $appointmentDate = !empty($this->appointment->appointment_date)
            ? Carbon::parse($this->appointment->appointment_date)->format('F j, Y')
            : 'N/A';
        $appointmentTime = !empty($this->appointment->appointment_time)
            ? Carbon::parse($this->appointment->appointment_time)->format('h:i A')
            : 'N/A';

        $content = BrandedMail::renderBody('emails.bodies.appointment_response', [
            'appointment' => $this->appointment,
            'client' => $this->client,
            'sender' => $this->sender,
            'accepted' => $accepted,
            'seekNext' => $seekNext,
            'appointmentDate' => $appointmentDate,
            'appointmentTime' => $appointmentTime,
        ]);

        if ($seekNext) {
            $headerTitle = 'Client Requested Next Appointment';
            $subject = 'Next appointment requested by ' . $this->client->name;
        } else {
            $headerTitle = $accepted ? 'Appointment Accepted' : 'Appointment Declined';
            $subject = $accepted
                ? 'Appointment Accepted by ' . $this->client->name
                : 'Appointment Declined by ' . $this->client->name;
        }

        $mail = $this->from(BrandedMail::alertsFromAddress(), BrandedMail::alertsFromName($this->client->name))
            ->subject($subject)
            ->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'));

        BrandedMail::applyDefaultReplyTo($mail);

        return $mail;
    }
}
