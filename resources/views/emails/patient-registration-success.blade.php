<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - Your Account Credentials</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9fafb;">
        <div style="background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="color: #2563eb; margin-bottom: 20px;">Registration Successful!</h2>
            
            <p>Dear {{ $data['salutation'] ?? '' }} {{ $data['full_name'] ?? 'Member' }},</p>
            
            <p>Thank you for registering with us! Your account has been successfully created. Below are your login credentials:</p>
            
            <div style="background-color: #f3f4f6; padding: 20px; border-radius: 6px; margin: 25px 0;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold; color: #4b5563;">Login ID (Mobile Number):</td>
                        <td style="padding: 10px 0; color: #1f2937;">{{ $data['mobile_country_code'] ?? '' }}{{ $data['mobile_number'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold; color: #4b5563;">Password:</td>
                        <td style="padding: 10px 0; color: #1f2937; font-family: 'Courier New', monospace; font-size: 16px; font-weight: bold;">{{ $data['password'] ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
            
            <div style="background-color: #dbeafe; border-left: 4px solid #3b82f6; padding: 15px; margin: 25px 0; border-radius: 4px;">
                <p style="margin: 0; color: #1e40af;">
                    <strong>ℹ️ Account Status:</strong> Your account is currently pending approval. You will be able to login once your account has been approved by our team.
                </p>
            </div>
            
            <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 25px 0; border-radius: 4px;">
                <p style="margin: 0; color: #92400e;">
                    <strong>⚠️ Security Notice:</strong> For your security, please change your password after your first login.
                </p>
            </div>
            
            <div style="margin: 25px 0;">
                <h3 style="color: #374151; font-size: 16px; margin-bottom: 10px;">Your Account Details:</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280;">Full Name:</td>
                        <td style="padding: 8px 0; color: #1f2937; font-weight: 500;">{{ $data['salutation'] ?? '' }} {{ $data['full_name'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280;">Email:</td>
                        <td style="padding: 8px 0; color: #1f2937; font-weight: 500;">{{ $data['email'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280;">Mobile:</td>
                        <td style="padding: 8px 0; color: #1f2937; font-weight: 500;">{{ $data['mobile_country_code'] ?? '' }}{{ $data['mobile_number'] ?? 'N/A' }}</td>
                    </tr>
                    @if(isset($data['company_name']) && !empty($data['company_name']))
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280;">Company:</td>
                        <td style="padding: 8px 0; color: #1f2937; font-weight: 500;">{{ $data['company_name'] }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            
            <p style="margin-top: 30px;">If you did not register for this account or have any questions, please contact our support team immediately.</p>
            
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

