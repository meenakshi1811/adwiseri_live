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

        <p style="margin:25px 0;">
            <a href="{{ $appointment->accept_url }}" style="background:#28a745;color:#fff;padding:10px 16px;border-radius:6px;text-decoration:none;margin-right:20px;display:inline-block;">
                Accept Appointment
            </a>
            <a href="{{ $appointment->decline_url }}" style="background:#dc3545;color:#fff;padding:10px 16px;border-radius:6px;text-decoration:none;margin-left:20px;display:inline-block;">
                Decline Appointment
            </a>
        </p>

        @if(!empty($appointment->remarks))
            <p><strong>Meeting purpose:</strong> {{ $appointment->remarks }}</p>
        @endif

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
