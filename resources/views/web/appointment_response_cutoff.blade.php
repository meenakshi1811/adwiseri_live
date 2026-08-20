<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Appointment Cut-off' }}</title>
    <style>
        body {
            margin: 0;
            font-family: 'Lato', Arial, sans-serif;
            background: #f4f6fb;
            color: #1f2937;
        }
        .wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            width: 100%;
            max-width: 620px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(17, 24, 39, 0.08);
            padding: 30px;
            text-align: center;
        }
        .badge {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            font-weight: 700;
            color: #fff;
            background: #f59e0b;
        }
        h1 {
            margin: 0 0 10px;
            font-size: 28px;
            color: #695EEE;
        }
        p {
            margin: 0;
            font-size: 16px;
            line-height: 1.6;
            color: #4b5563;
        }
        .prompt {
            margin-top: 14px;
            font-weight: 600;
            color: #374151;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
            margin-top: 28px;
        }
        .actions a {
            display: inline-block;
            min-width: 190px;
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
        }
        .btn-secondary {
            background: #fff;
            color: #695EEE;
            border: 1px solid #695EEE;
        }
        .btn-primary {
            background: #695EEE;
            color: #fff;
            border: 1px solid #695EEE;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="badge">!</div>
        <h1>{{ $title ?? 'Oops!' }}</h1>
        <p>{{ $subtitle ?? 'Cut-off time has reached for this appointment.' }}</p>
        <p class="prompt">{{ $prompt ?? 'Do you still want to notify the consultant or seek another appointment?' }}</p>

        <div class="actions">
            <a href="{{ $dontNotifyUrl }}" class="btn-secondary">Don't Notify</a>
            <a href="{{ $seekNextUrl }}" class="btn-primary">Seek Next Appointment</a>
        </div>
    </div>
</div>
</body>
</html>
