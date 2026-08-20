<p style="margin:0 0 12px 0;">Dear {{ $client->name }},</p>
<p style="margin:0 0 16px 0;">
    {{ $sender->name }} has invited you for an appointment.
    Please confirm the proposed meeting using one of the options below.
</p>
<p style="margin:0 0 8px 0;"><strong>Appointment Date:</strong> {{ !empty($appointment->appointment_date) ? \Carbon\Carbon::parse($appointment->appointment_date)->format('F j, Y') : 'N/A' }}</p>
<p style="margin:0 0 8px 0;"><strong>Appointment Time:</strong> {{ !empty($appointment->appointment_time) ? \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') : 'N/A' }}</p>
<p style="margin:0 0 20px 0;"><strong>Meeting Purpose:</strong> {{ !empty($appointment->remarks) ? $appointment->remarks : 'N/A' }}</p>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 20px 0;">
    <tr>
        <td style="padding-right:14px;">
            <a href="{{ $appointment->accept_url }}" style="background:#695EEE;color:#fff;padding:10px 16px;border-radius:6px;text-decoration:none;display:inline-block;">Accept Appointment</a>
        </td>
        <td>
            <a href="{{ $appointment->decline_url }}" style="background:#dc3545;color:#fff;padding:10px 16px;border-radius:6px;text-decoration:none;display:inline-block;">Decline Appointment</a>
        </td>
    </tr>
</table>

<p style="margin:0 0 16px 0;">Once you respond, the sender will see your decision in their appointment records.</p>
<p style="margin:0;">Best regards,<br><strong>{{ $sender->name }}</strong></p>
