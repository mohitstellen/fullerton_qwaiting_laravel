<!DOCTYPE html>
<html>

<head>
    <title>Appointment Questionnaire</title>
</head>

<body>
    <div style="background:#e8e8e8;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen,Ubuntu,Cantarell,Fira Sans,Droid Sans,Helvetica Neue,sans-serif;font-size:13px;line-height:1.4;padding:2% 7%">

        <img id="Qwaiting" src="{{ $logo }}" alt="logo" class="CToWUd" style="vertical-align:middle;" width="100">

        <div style="background:#fff;border-top-color:#6e8cce;border-top-style:solid;border-top-width:4px;margin:25px auto; border-radius: 8px;">
            <div style="border-color:#e5e5e5;border-style:none solid solid;border-width:2px;padding:7%">
                <div>
                    <h1 style="color: #333333; margin: 0 0 20px 0; font-size: 24px; text-align: center;">Appointment Questionnaire</h1>
                    <p style="color: #555555; line-height: 1.6;">Hello <strong>{{ $name }}</strong>,</p>
                    <p style="color: #555555; line-height: 1.6;">Please click the link below to complete your appointment questionnaire.</p>

                    <div style="background-color: #f0f7ff; border: 1px dashed #2563eb; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0;">
                        <a href="{{ $url }}" style="font-size: 18px; font-weight: bold; color: #2563eb; text-decoration: none; word-break: break-all;">{{ $url }}</a>
                    </div>

                    <p style="color: #555555; line-height: 1.6;">If you did not request this, please ignore this email.</p>
                </div>
            </div>
        </div>
        <div style="text-align:center" align="center">
            <p style="color:#999;font-size:11px;line-height:1.4;margin:5px 0">Copyright {{ date('Y') }} © Qwaiting Inc. All Rights Reserved.</p>
        </div>
    </div>
</body>

</html>