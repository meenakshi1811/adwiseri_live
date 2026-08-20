<p style="margin:0 0 12px 0;">Dear {{ $sender->name }},</p>

@if($accepted)
    <p style="margin:0 0 16px 0;">
        <strong>{{ $client->name }}</strong> has accepted your appointment invitation.
    </p>
@else
    <p style="margin:0 0 16px 0;">
        <strong>{{ $client->name }}</strong> has declined your appointment invitation.
    </p>
@endif

<p style="margin:0 0 8px 0;"><strong>Client:</strong> {{ $client->name }}</p>
<p style="margin:0 0 8px 0;"><strong>Appointment Date:</strong> {{ $appointmentDate }}</p>
<p style="margin:0 0 8px 0;"><strong>Appointment Time:</strong> {{ $appointmentTime }}</p>
<p style="margin:0 0 20px 0;"><strong>Meeting Purpose:</strong> {{ !empty($appointment->remarks) ? $appointment->remarks : 'N/A' }}</p>

<p style="margin:0 0 16px 0;">
    You can view this appointment in your
    <a href="{{ route('my_settings') }}#appointment">Appointment Scheduler</a> records.
</p>

<p style="margin:0;">Best regards,<br><strong>Adwiseri</strong></p>
