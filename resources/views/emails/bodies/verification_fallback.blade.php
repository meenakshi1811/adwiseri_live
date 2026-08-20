@if(isset($data->password))
    <p style="margin:0 0 12px 0;"><strong>Hello {{ $data->name }}</strong></p>
    <p style="margin:0 0 16px 0;">Your OTP for password recovery is <strong>{{ $data->otp }}</strong>.</p>
@elseif(isset($data->message))
    <p style="margin:0 0 12px 0;"><strong>Hello,</strong></p>
    <p style="margin:0 0 16px 0;">{{ $data->name }} sent a message via the Adwiseri contact form.</p>
    <p style="margin:0 0 6px 0;"><strong>Name:</strong> {{ $data->name }}</p>
    <p style="margin:0 0 6px 0;"><strong>Email:</strong> {{ $data->email }}</p>
    <p style="margin:0 0 6px 0;"><strong>Phone:</strong> {{ \App\Support\PhoneNumber::displayE164($data->phone) }}</p>
    <p style="margin:0 0 6px 0;"><strong>Country:</strong> {{ $data->country }}</p>
    <p style="margin:0 0 12px 0;"><strong>City:</strong> {{ $data->city }}</p>
    <p style="margin:0;"><strong>Message:</strong><br>{{ $data->message }}</p>
@elseif(isset($data->how_did_hear))
    <p style="margin:0 0 12px 0;"><strong>Hello,</strong></p>
    <p style="margin:0 0 16px 0;">A demo request was submitted on adwiseri.com.</p>
    <p style="margin:0 0 6px 0;"><strong>Name:</strong> {{ $data->name }}</p>
    <p style="margin:0 0 6px 0;"><strong>Email:</strong> {{ $data->email }}</p>
    <p style="margin:0 0 6px 0;"><strong>Phone:</strong> {{ \App\Support\PhoneNumber::displayE164($data->phone) }}</p>
    <p style="margin:0 0 6px 0;"><strong>Country:</strong> {{ $data->country }}</p>
    <p style="margin:0 0 6px 0;"><strong>City:</strong> {{ $data->city }}</p>
    <p style="margin:0 0 6px 0;"><strong>Company:</strong> {{ $data->company_name ?? '-' }}</p>
    <p style="margin:0;"><strong>How they heard about us:</strong> {{ $data->how_did_hear }}</p>
@else
    <p style="margin:0 0 12px 0;"><strong>Hello {{ $data->name }}</strong></p>
    <p style="margin:0 0 16px 0;">Thanks for joining Adwiseri. Your email verification OTP is <strong>{{ $data->otp }}</strong>.</p>
    <p style="margin:0 0 10px 0;"><strong>Have a question?</strong></p>
    <p style="margin:0 0 10px 0;">Check our <strong><a href="https://adwiseri.com/faqs">FAQ Page</a></strong> for a quick answer.</p>
    <p style="margin:0;">You can always contact support team via email <a href="mailto:{{ $supportEmail ?? \App\Support\BrandedMail::supportEmail() }}">{{ $supportEmail ?? \App\Support\BrandedMail::supportEmail() }}</a>.</p>
    @include('emails.partials.signature')
@endif
