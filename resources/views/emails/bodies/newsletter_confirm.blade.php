<p style="margin:0 0 12px 0;"><strong>Hello,</strong></p>
<p style="margin:0 0 16px 0;">Thank you for subscribing to Adwiseri updates. Your email has been added to our mailing list.</p>
<p style="margin:0 0 10px 0;"><strong>Have a question?</strong></p>
<p style="margin:0;">Check our <strong><a href="https://adwiseri.com/faqs">FAQ Page</a></strong> or contact support team via email <a href="mailto:{{ $supportEmail ?? \App\Support\BrandedMail::supportEmail() }}">{{ $supportEmail ?? \App\Support\BrandedMail::supportEmail() }}</a>.</p>
@include('emails.partials.signature')
