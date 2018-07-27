<html>
<head>
    <style>

    </style>
</head>

<body>
    <h1>Join {{$name}} on Informed 365</h1>
    <p>You have been invited to join the {{$type}} {{$name}} on Informed 365.</p>
    <p>To accept this invitation:
        <ol>
            <li>Navigate to <a href="https://app.informed365.com">https://app.informed365.com</a></li>
            <li>Log into your account. If you don't have an account you can crete a new account.</li>
            <li>Click the invitation link below</li>
        </ol>
    </p>
    <p>Please note you must be logged in to accept the invitation.</p>
    <a href="{{config('mail.fronturl')}}/invitation/{{$type}}/{{$token}}">Accept invitation</a>
</body>
</html>