<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333; padding: 20px;">
    <h2>@lang('portal::app.mail.contact-us.heading')</h2>

    <p><strong>@lang('portal::app.home.contact-us.name'):</strong> {{ $data['name'] }}</p>
    <p><strong>@lang('portal::app.home.contact-us.email'):</strong> {{ $data['email'] }}</p>
    @if (! empty($data['contact']))
        <p><strong>@lang('portal::app.home.contact-us.phone'):</strong> {{ $data['contact'] }}</p>
    @endif
    <p><strong>@lang('portal::app.home.contact-us.message'):</strong></p>
    <div style="background: #f4f4f5; padding: 15px; border-radius: 8px;">
        {{ $data['message'] }}
    </div>
</body>
</html>

