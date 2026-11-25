<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Login OTP</title></head>
<body style="margin:0;padding:24px;background:#f4f5f9;font-family:Arial,Helvetica,sans-serif;color:#1f2430;">
    <div style="max-width:480px;margin:0 auto;background:#fff;border-radius:10px;padding:28px;">
        <h2 style="margin:0 0 12px;color:#332c6d;">Vyora login OTP</h2>
        <p style="margin:0 0 16px;">Hi {{ $name }},</p>
        <p style="margin:0 0 16px;">Use this one-time password to sign in:</p>
        <div style="font-size:32px;letter-spacing:8px;font-weight:700;text-align:center;background:#eceaff;color:#332c6d;border-radius:8px;padding:14px 0;">{{ $otp }}</div>
        <p style="margin:16px 0 0;font-size:13px;color:#6b7280;">This OTP is valid for {{ $minutes }} minutes. Do not share it with anyone. If you did not request it, you can ignore this email.</p>
    </div>
</body>
</html>
