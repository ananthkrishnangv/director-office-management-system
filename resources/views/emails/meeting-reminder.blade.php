<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Meeting Reminder</title>
</head>
<body style="font-family: 'Noto Sans', sans-serif; background: #f5f5f5; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="background: #0078d4; color: white; padding: 20px; text-align: center;">
            <h1 style="margin: 0;">🔔 Meeting Reminder</h1>
        </div>
        <div style="padding: 30px;">
            <p>This is a reminder for your upcoming meeting:</p>
            
            <div style="background: #f0f0f0; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <h2 style="margin-top: 0; color: #333;">{{ $meeting->title }}</h2>
                <p><strong>📅 Date:</strong> {{ $meeting->start_time->format('l, d M Y') }}</p>
                <p><strong>🕐 Time:</strong> {{ $meeting->start_time->format('h:i A') }} - {{ $meeting->end_time->format('h:i A') }}</p>
                @if($meeting->location)
                <p><strong>📍 Location:</strong> {{ $meeting->location }}</p>
                @endif
                @if($meeting->online_link)
                <p><strong>🔗 Online Link:</strong> <a href="{{ $meeting->online_link }}">Join Meeting</a></p>
                @endif
            </div>
            
            @if($meeting->description)
            <p><strong>Description:</strong> {{ $meeting->description }}</p>
            @endif
            
            <p style="margin-top: 30px;">Best regards,<br><strong>Director's Office</strong><br>CSIR-SERC</p>
        </div>
    </div>
</body>
</html>
