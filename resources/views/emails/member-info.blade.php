<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Information</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2563eb;">Member Information</h2>
        
        <p>Dear {{ $data['member_name'] ?? 'Member' }},</p>
        
        <p>This email contains your member information:</p>
        
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Name:</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">{{ $data['member_name'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Email:</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">{{ $data['member_email'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Mobile:</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">{{ $data['member_mobile'] ?? 'N/A' }}</td>
            </tr>
        </table>
        
        <p>If you have any questions, please contact our support team.</p>
        
        <p>Best regards,<br>
        Fullerton Health Team</p>
    </div>
</body>
</html>


