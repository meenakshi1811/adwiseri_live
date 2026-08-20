<?php

namespace App\Mail;

use App\Support\BrandedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $otp;
    public $ttlMinutes;

    public function __construct($name, $otp, int $ttlMinutes = 5)
    {
        $this->name = $name;
        $this->otp = $otp;
        $this->ttlMinutes = $ttlMinutes;
    }

    public function build()
    {
        $data = (object) [
            'name' => $this->name,
            'otp' => $this->otp,
            'ttlMinutes' => $this->ttlMinutes,
        ];

        $content = BrandedMail::renderBody('emails.bodies.admin_2fa', ['data' => $data]);

        $mail = $this->subject('Your Adwiseri Admin Login OTP')
            ->view(BrandedMail::LAYOUT, [
                'content' => $content,
                'headerTitle' => 'Admin Login Verification',
            ]);

        // Sender: alerts@adwiseri.com | Reply-To: care@adwiseri.com
        return BrandedMail::applyPlatformEnvelope($mail);
    }
}
