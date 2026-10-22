<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $notice->title }}</title></head>
<body style="margin:0;padding:24px;background:#06111f;color:#eaf2fa;font-family:Arial,sans-serif">
    <main style="max-width:620px;margin:0 auto;padding:28px;border:1px solid #173a57;border-radius:14px;background:#0b1b2c">
        <p style="margin:0 0 10px;color:#46d6c2;font-size:12px;font-weight:bold;letter-spacing:1px">MYTRADEX · {{ $notice->category === 'action' ? 'ACCOUNT UPDATE' : 'ADMINISTRATOR MESSAGE' }}</p>
        <h1 style="margin:0 0 18px;font-size:22px">{{ $notice->title }}</h1>
        <p style="margin:0;color:#c3d3e2;font-size:15px;line-height:1.7;white-space:pre-line">{{ $notice->body }}</p>
        <p style="margin:24px 0 0;color:#91a8bd;font-size:12px">Sign in to your MyTradeX account to view this update in your dashboard.</p>
    </main>
</body>
</html>
