<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome to Meter Calibration Laboratory System</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif;">

<h2>Welcome {{ $customer->contact_person }}</h2>

<p>Dear {{ $customer->contact_person }},</p>

<p>
Your customer account has been successfully created in the
<strong>Meter Calibration Laboratory System (MCL)</strong>.
</p>

<p>
You can now log in and submit calibration requests, monitor progress,
download certificates and view your calibration history.
</p>

<table cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;">
    <tr>
        <td><strong>Company</strong></td>
        <td>{{ $customer->company_name }}</td>
    </tr>

    <tr>
        <td><strong>User ID / Email</strong></td>
        <td>{{ $user->email }}</td>
    </tr>

    <tr>
        <td><strong>Temporary Password</strong></td>
        <td>{{ $password }}</td>
    </tr>
</table>

<br>

<p>
<strong>Security Notice</strong>
</p>

<p style="color:red;">
For your security, please change your password immediately after your first login.
Do not share your login credentials with anyone.
</p>

<p>

<b>Login URL</b><br>

<a href="{{ url('/login') }}">
{{ url('/login') }}
</a>

</p>

<br>

Regards,<br>

<b>Meter Calibration Laboratory</b><br>
Kenya Power & Lighting Company PLC

</body>
</html>
