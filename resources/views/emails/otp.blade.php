<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #F0F0F0;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background: #FFFFFF;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        .email-header {
            background: linear-gradient(135deg, #121212 0%, #1F1F1F 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .logo {
            font-size: 32px;
            font-weight: 700;
            color: #FFFFFF;
            margin: 0;
        }
        .logo-accent {
            color: #B5F23C;
        }
        .email-body {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 20px;
            font-weight: 600;
            color: #121212;
            margin: 0 0 16px;
        }
        .message {
            font-size: 15px;
            color: #64748B;
            line-height: 1.6;
            margin: 0 0 30px;
        }
        .otp-container {
            background: #F8FAFC;
            border: 2px dashed #E5EAF0;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .otp-label {
            font-size: 13px;
            font-weight: 600;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 12px;
        }
        .otp-code {
            font-size: 42px;
            font-weight: 700;
            color: #121212;
            letter-spacing: 8px;
            margin: 0;
            font-family: 'Courier New', monospace;
        }
        .otp-expiry {
            font-size: 13px;
            color: #94A3B8;
            margin: 12px 0 0;
        }
        .warning-box {
            background: #FFF7ED;
            border-left: 4px solid #F87216;
            padding: 16px 20px;
            border-radius: 8px;
            margin: 30px 0;
        }
        .warning-text {
            font-size: 14px;
            color: #C2410C;
            margin: 0;
            line-height: 1.5;
        }
        .email-footer {
            background: #F8FAFC;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #E5EAF0;
        }
        .footer-text {
            font-size: 13px;
            color: #94A3B8;
            margin: 0 0 8px;
        }
        .footer-link {
            color: #B5F23C;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1 class="logo">Squash<span class="logo-accent">Pro</span></h1>
        </div>
        
        <div class="email-body">
            <h2 class="greeting">Hello {{ $userName }},</h2>
            
            @if($purpose === 'registration')
                <p class="message">
                    Thank you for registering with Squash Pro! To complete your account setup, please verify your email address using the code below.
                </p>
            @elseif($purpose === 'login')
                <p class="message">
                    We received a login request for your Squash Pro account. Use the verification code below to complete your login.
                </p>
            @elseif($purpose === 'password_reset')
                <p class="message">
                    We received a request to reset your Squash Pro account password. Use the code below to proceed with password reset.
                </p>
            @else
                <p class="message">
                    Please use the verification code below to complete your action on Squash Pro.
                </p>
            @endif
            
            <div class="otp-container">
                <p class="otp-label">Your Verification Code</p>
                <p class="otp-code">{{ $otp }}</p>
                <p class="otp-expiry">This code expires in {{ $expiryMinutes }} minutes</p>
            </div>
            
            <div class="warning-box">
                <p class="warning-text">
                    <strong>Security Notice:</strong> Never share this code with anyone. Squash Pro staff will never ask for your verification code.
                </p>
            </div>
            
            <p class="message">
                If you didn't request this code, please ignore this email or contact our support team immediately.
            </p>
        </div>
        
        <div class="email-footer">
            <p class="footer-text">© {{ date('Y') }} Squash Pro. All rights reserved.</p>
            <p class="footer-text">
                Need help? <a href="mailto:support@squashpro.com" class="footer-link">Contact Support</a>
            </p>
        </div>
    </div>
</body>
</html>
