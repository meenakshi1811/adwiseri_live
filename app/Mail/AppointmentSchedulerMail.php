<?php

namespace App\Mail;

use App\Support\BrandedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentSchedulerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $appointment,
        public $client,
        public $sender
    ) {
    }

    public function build()
    {
        $content = BrandedMail::renderBody('emails.bodies.appointment', [
            'appointment' => $this->appointment,
            'client' => $this->client,
            'sender' => $this->sender,
        ]);

        $headerTitle = 'Appointment Invitation';
        $subscriberName = trim((string) ($this->sender->name ?? ''));

        $mail = $this->from(BrandedMail::alertsFromAddress(), BrandedMail::alertsFromName($subscriberName))
            ->subject('Appointment Invitation - Response Required')
            ->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'));

        if (!empty($this->sender->email)) {
            BrandedMail::applySubscriberReplyTo($mail, $this->sender->email, $this->sender->name);
        } else {
            BrandedMail::applyDefaultReplyTo($mail);
        }

        return $mail;
    }
}
