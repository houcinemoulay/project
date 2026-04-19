<!DOCTYPE html>
<html>
<head>
    <title>Contact Message</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;">
        <h2 style="color: #0f3460;">New Contact Message</h2>
        <p><strong>From:</strong> {{ $data['name'] }} ({{ $data['email'] }})</p>
        <p><strong>Subject:</strong> {{ $data['subject'] }}</p>
        <hr style="border: 0; border-top: 1px solid #eee;">
        <p><strong>Message:</strong></p>
        <p style="white-space: pre-wrap;">{{ $data['message'] }}</p>
    </div>
</body>
</html>
