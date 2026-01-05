<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Appointment Status Update</title>
</head>
<body style="font-family: 'Noto Sans', sans-serif; background: #f5f5f5; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="background: #d13438; color: white; padding: 20px; text-align: center;">
            <h1 style="margin: 0;">Appointment Update</h1>
        </div>
        <div style="padding: 30px;">
            <p>Dear {{ $appointment->requester_name }},</p>
            <p>We regret to inform you that your appointment request could not be approved at this time.</p>
            
            <div style="background: #f0f0f0; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <p><strong>📋 Purpose:</strong> {{ $appointment->purpose }}</p>
                <p><strong>📅 Requested Date:</strong> {{ $appointment->requested_date->format('d M Y') }}</p>
                @if($appointment->rejection_reason)
                <p><strong>📝 Reason:</strong> {{ $appointment->rejection_reason }}</p>
                @endif
            </div>
            
            <p>You may submit a new request for a different date and time.</p>
            
            <p style="margin-top: 30px;">Best regards,<br><strong>Director's Office</strong><br>CSIR-SERC</p>
        </div>
        <div style="background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666;">
            Council of Scientific & Industrial Research - Structural Engineering Research Centre
        </div>
    </div>
</body>
</html>
