<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7fb;
            color: #222;
            margin: 0;
            padding: 40px 16px;
        }
        .error-card {
            max-width: 560px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 28px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        }
        h1 {
            margin: 0 0 12px;
            font-size: 22px;
            color: #695EEE;
        }
        p {
            margin: 0;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <h1>Something went wrong</h1>
        <p>{{ $message ?? 'An unexpected error occurred. Please try again later.' }}</p>
    </div>
</body>
</html>
