<div style="margin-bottom:20px;text-align:center;">
    <img src="{{ url('web_assets/images/handshake.png') }}" width="150" alt="Welcome" style="border:1px solid #e5e7eb;border-radius:7px;padding:15px 25px;">
</div>

@if(!empty($content))
    {!! $content !!}
@else
    <p style="margin:0 0 16px 0;"><strong>Hello {{ $data->name }},</strong></p>
    @if(isset($data->subscription) && strtolower((string) $data->subscription) === 'paid')
        <p style="margin:0 0 16px 0;">Congratulations! Your registration at <strong>Adwiseri</strong> is successful and your subscription is active.</p>
        <p style="margin:0 0 8px 0;"><strong>Subscription Type (Plan):</strong> {{ $data->subscription_type ?? $data->plan_name ?? '-' }}</p>
        <p style="margin:0 0 8px 0;"><strong>Start Date:</strong> {{ $data->start_date ?? '-' }}</p>
        <p style="margin:0 0 8px 0;"><strong>End Date:</strong> {{ $data->end_date ?? '-' }}</p>
        <p style="margin:0 0 16px 0;"><strong>Paid Amount:</strong> USD {{ $data->paid_amount ?? $data->amount ?? '0.00' }}</p>
        @if(!empty($data->invoice_id) && !empty($data->token))
            <p style="margin:0 0 16px 0;">View invoice: <a href="{{ route('invoice_preview', $data->invoice_id . '/' . $data->token) }}">Click here</a></p>
        @endif
    @else
        <p style="margin:0 0 16px 0;"><strong>{{ $data->plan_name ?? 'Free Plan' }}</strong> plan is activated on your account.</p>
        <p style="margin:0 0 16px 0;">Subscription details are as follows:</p>
        <p style="margin:0 0 8px 0;"><strong>Plan Name:</strong> {{ $data->plan_name ?? 'Free Plan' }}</p>
        <p style="margin:0 0 8px 0;"><strong>Duration:</strong> {{ $data->duration ?? '-' }}</p>
        <p style="margin:0 0 16px 0;"><strong>Paid Amount:</strong> USD {{ $data->paid_amount ?? $data->amount ?? '0.00' }}</p>
    @endif
    <p style="margin:0 0 10px 0;"><strong>Have a question?</strong></p>
    <p style="margin:0 0 10px 0;">Check our <strong><a href="https://adwiseri.com/faqs">FAQ Page</a></strong> for a quick answer.</p>
    <p style="margin:0;">You can contact support team via email <a href="mailto:{{ $supportEmail ?? \App\Support\BrandedMail::supportEmail() }}">{{ $supportEmail ?? \App\Support\BrandedMail::supportEmail() }}</a> or by raising ticket.</p>
    <p style="margin:16px 0 24px 0;">{!! \App\Support\BrandedMail::emailSignatureHtml() !!}</p>
@endif
