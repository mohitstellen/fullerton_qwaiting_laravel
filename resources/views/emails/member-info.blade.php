<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Account Information</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9fafb;">
        <div style="background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="color: #2563eb; margin-bottom: 20px;">Your Account Information</h2>
            
            <p>Dear {{ $data['member_name'] ?? 'Member' }},</p>
            
            <p>Your account credentials for Fullerton Qwaiting system are provided below.</p>
            
            <div style="background-color: #f3f4f6; padding: 20px; border-radius: 6px; margin: 25px 0;">
                <h3 style="color: #374151; font-size: 16px; margin-bottom: 15px;">Login Credentials:</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold; color: #4b5563;">Email:</td>
                        <td style="padding: 10px 0; color: #1f2937;">{{ $data['member_email'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold; color: #4b5563;">Login ID (Mobile Number):</td>
                        <td style="padding: 10px 0; color: #1f2937;">{{ $data['login_id'] ?? $data['member_mobile'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold; color: #4b5563;">Password:</td>
                        <td style="padding: 10px 0; color: #1f2937; font-family: 'Courier New', monospace; font-size: 16px;">{{ $data['password'] ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
            
            <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 25px 0; border-radius: 4px;">
                <p style="margin: 0; color: #92400e;">
                    <strong>⚠️ Security Notice:</strong> For your security, please change your password after logging in. A new password has been generated for you.
                </p>
            </div>
            
            <div style="margin: 25px 0;">
                <h3 style="color: #374151; font-size: 16px; margin-bottom: 10px;">Your Account Details:</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; border-bottom: 1px solid #e5e7eb;">Full Name:</td>
                        <td style="padding: 8px 0; color: #1f2937; font-weight: 500; border-bottom: 1px solid #e5e7eb;">{{ $data['member_name'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; border-bottom: 1px solid #e5e7eb;">Mobile:</td>
                        <td style="padding: 8px 0; color: #1f2937; font-weight: 500; border-bottom: 1px solid #e5e7eb;">{{ $data['member_mobile'] ?? 'N/A' }}</td>
                    </tr>
                    @if(isset($data['company_name']) && $data['company_name'] !== 'N/A')
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; border-bottom: 1px solid #e5e7eb;">Company:</td>
                        <td style="padding: 8px 0; color: #1f2937; font-weight: 500; border-bottom: 1px solid #e5e7eb;">{{ $data['company_name'] }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            
            <p style="margin-top: 30px;">If you did not request these credentials or have any questions, please contact our support team immediately.</p>
            
            <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                <p style="margin: 0;">Best regards,</p>
                <p style="margin: 5px 0 0 0; font-weight: bold; color: #2563eb;">Fullerton Health Team</p>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 20px; color: #6b7280; font-size: 12px;">
            <p>This is an automated email. Please do not reply to this message.</p>
        </div>
    </div>
</body>
</html>
