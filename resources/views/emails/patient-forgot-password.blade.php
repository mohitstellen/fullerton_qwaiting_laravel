<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fullerton Health - Forgot Login ID or Password</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9fafb;">
        <div style="background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="color: #2563eb; margin-bottom: 20px;">Fullerton Health - Forgot Login ID or Password</h2>
            
            <p>Dear {{ $data['salutation'] ?? 'Mr' }}. {{ $data['full_name'] ?? 'Member' }},</p>
            
            <p>We have received your Forgot Login ID / Password notification and have set a temporary password <strong>{{ $data['temporary_password'] ?? 'N/A' }}</strong> for you.</p>
            
            <div style="background-color: #f3f4f6; padding: 20px; border-radius: 6px; margin: 25px 0;">
                <p style="margin: 0 0 15px 0; color: #4b5563; font-weight: bold;">Your Temporary Password:</p>
                <p style="margin: 0; color: #1f2937; font-family: 'Courier New', monospace; font-size: 20px; font-weight: bold; text-align: center; padding: 10px; background-color: #ffffff; border-radius: 4px;">{{ $data['temporary_password'] ?? 'N/A' }}</p>
            </div>
            
            <p style="margin: 25px 0;">Please login on our website at this link: <a href="{{ $data['login_url'] ?? '#' }}" style="color: #2563eb; text-decoration: underline;">{{ $data['login_url'] ?? 'Login Page' }}</a></p>
            
            <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 25px 0; border-radius: 4px;">
                <p style="margin: 0; color: #92400e;">
                    <strong>⚠️ Important:</strong> You will be required to change your password after logging in with the temporary password.
                </p>
            </div>
            
            <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                <p style="margin: 0;">Best Regards,</p>
                <p style="margin: 5px 0 0 0; font-weight: bold; color: #2563eb;">Fullerton Health</p>
                <p style="margin: 5px 0 0 0;">
                    <a href="www.fullertonhealth.com" style="color: #2563eb; text-decoration: underline;">www.fullertonhealth.com</a>
                </p>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 20px; color: #6b7280; font-size: 12px;">
            <p>This is a computer generated email. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>

