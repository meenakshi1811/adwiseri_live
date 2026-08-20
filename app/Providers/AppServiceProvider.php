<?php

namespace App\Providers;

use App\Support\BrandedMail;
use App\Support\PhoneNumber;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::share('copyrightYears', BrandedMail::copyrightYears());
        View::share('supportEmail', BrandedMail::supportEmail());

        // Ensure platform default Reply-To is registered even when MAIL_REPLY_TO_ADDRESS is blank in .env
        Mail::alwaysReplyTo(BrandedMail::supportEmail(), BrandedMail::supportName());

        // Safety net: if an outbound message somehow has no Reply-To, force care@adwiseri.com
        Event::listen(MessageSending::class, function (MessageSending $event) {
            $message = $event->message;
            if (!is_object($message) || !method_exists($message, 'getReplyTo')) {
                return;
            }

            $existing = $message->getReplyTo();
            if (empty($existing)) {
                $address = BrandedMail::supportEmail();
                $name = BrandedMail::supportName();
                if (method_exists($message, 'setReplyTo')) {
                    $message->setReplyTo([$address => $name]);
                }
            }

            // Ensure invoice / platform auto-emails always Bcc the alerts archive mailbox.
            $fromAddresses = [];
            if (method_exists($message, 'getFrom')) {
                foreach ($message->getFrom() ?? [] as $fromAddress) {
                    if (is_object($fromAddress) && method_exists($fromAddress, 'getAddress')) {
                        $fromAddresses[] = strtolower(trim((string) $fromAddress->getAddress()));
                    }
                }
            }

            $subject = method_exists($message, 'getSubject') ? (string) $message->getSubject() : '';
            $isInvoiceEmail = stripos($subject, 'invoice') !== false;

            $alertsFrom = strtolower(trim(BrandedMail::alertsFromAddress()));
            if (!$isInvoiceEmail && $alertsFrom !== '' && in_array($alertsFrom, $fromAddresses, true)) {
                BrandedMail::ensureAlertsBccOnMessage($message);
            }

            BrandedMail::ensureAttachmentDispositionOnMessage($message);
        });

        Validator::extend('phone_intl', function ($attribute, $value) {
            return PhoneNumber::isValid($value);
        });

        Validator::replacer('phone_intl', function ($message, $attribute) {
            return 'Please enter a valid contact number (digits only, up to 10 digits after country code).';
        });
    }
}
