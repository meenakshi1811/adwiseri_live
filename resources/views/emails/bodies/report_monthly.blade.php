<p style="margin:0 0 14px 0;"><strong>Hello {{ $data->name }},</strong></p>
<p style="margin:0 0 14px 0;">Adwiseri monthly reports for Applications and Invoices have been generated. Download them using the links below.</p>

@if(isset($data->application) || isset($data->invoice))
    <p style="margin:0 0 8px 0;"><strong>Report Modules Included:</strong></p>
    <ul style="margin:0 0 16px 18px;padding:0;">
        @if(isset($data->application))
            <li>Applications Module</li>
        @endif
        @if(isset($data->invoice))
            <li>Invoices Module</li>
        @endif
    </ul>
@endif

@if(isset($data->invoice) || isset($data->application))
    <p style="margin:0 0 8px 0;"><strong>Download Reports:</strong></p>
    <ul style="margin:0 0 20px 18px;padding:0;">
        @if(isset($data->invoice))
            <li><a href="{{ asset('public/Exports/User'.$data->id.'/'.$data->invoice) }}" download="Invoice_Report">Download Invoice Report</a></li>
        @endif
        @if(isset($data->application))
            <li><a href="{{ asset('public/Exports/User'.$data->id.'/'.$data->application) }}" download="Application_Report">Download Application Report</a></li>
        @endif
    </ul>
@endif

@include('emails.partials.signature')
