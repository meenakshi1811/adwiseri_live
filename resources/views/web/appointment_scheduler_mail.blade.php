<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Appointment Invite Notification</title>
    <style>
        body { background:#F5F5F5; font-family: 'Lato', Arial, sans-serif; margin:0; }
    </style>
</head>
<body>
<div style="margin:30px 0;">
    <div style="border-radius:10px;width:55%;background:white;padding:30px;position:relative;margin:auto;">
        <h2 style="text-align:center;margin-top:0;">Appointment Invite Notification</h2>

        <p>Dear {{ $client->name }},</p>

        <p>
            {{ $sender->name }} has invited you for an appointment.
            Please confirm the proposed meeting using one of the options below.
        </p>

        <p style="margin:20px 0 10px;">
            <strong>Appointment Date:</strong>
            {{ !empty($appointment->appointment_date) ? \Carbon\Carbon::parse($appointment->appointment_date)->format('F j, Y') : 'N/A' }}
        </p>
        <p style="margin:10px 0;">
            <strong>Appointment Time:</strong>
            {{ !empty($appointment->appointment_time) ? \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') : 'N/A' }}
        </p>
        <p style="margin:10px 0 25px;">
            <strong>Meeting Purpose:</strong> {{ !empty($appointment->remarks) ? $appointment->remarks : 'N/A' }}
        </p>

        <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:25px 0;">
            <tr>
                <td style="padding-right:14px;">
                    <a href="{{ $appointment->accept_url }}" style="background:#28a745;color:#fff;padding:10px 16px;border-radius:6px;text-decoration:none;display:inline-block;">
                        Accept Appointment
                    </a>
                </td>
                <td style="padding-left:14px;">
                    <a href="{{ $appointment->decline_url }}" style="background:#dc3545;color:#fff;padding:10px 16px;border-radius:6px;text-decoration:none;display:inline-block;">
                        Decline Appointment
                    </a>
                </td>
            </tr>
        </table>

        <p>
            Once you respond, the sender will see your decision in their appointment records.
        </p>

        <p>
            Best regards,<br>
            <strong>{{ $sender->name }}</strong>
        </p>
    </div>
</div>

<footer style="padding:20px 0;"></footer>
</body>
</html>
