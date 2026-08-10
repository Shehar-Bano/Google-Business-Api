<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verification Code</title>
</head>
<body style="font-family: Arial, sans-serif; color: #222;">
    <p>Hello {{ $userName }},</p>

    <p>Your verification code is:</p>

    <h2 style="letter-spacing: 4px;">{{ $otp }}</h2>

    <p>This code expires in {{ $expiryMinutes }} minutes.</p>

    <p>Thanks,<br>{{ config('variables.templateName') }}</p>
</body>
</html>
