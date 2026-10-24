<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #0b1120; color: #e2e8f0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #1e293b; padding: 30px; border-radius: 10px; border-top: 4px solid #38bdf8; }
        h1 { color: #38bdf8; font-size: 1.5rem; }
        p { color: #cbd5e1; line-height: 1.6; }
        .button { display: inline-block; padding: 10px 20px; background: #38bdf8; color: #000; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px; }
        .footer { margin-top: 30px; font-size: 0.8rem; color: #64748b; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ $title }}</h1>
        <p>Hello {{ $userName }},</p>
        <p>{{ $messageBody }}</p>
        <a href="{{ route('dashboard') }}" class="button">Go to Dashboard</a>
        <div class="footer">
            © {{ date('Y') }} MyTradeX. All rights reserved. <br>
            This is an automated message, please do not reply.
        </div>
    </div>
</body>
</html>