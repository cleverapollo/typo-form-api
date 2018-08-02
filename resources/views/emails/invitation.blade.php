<html>
<head>
    <style>

    </style>
</head>

<body>
    <h1>Join {{$name}} on Informed 365</h1>
    <p>You have been invited to join the {{$type}} {{$name}} on the Informed 365 platform.</p>
    <p>To get started simply <a href="{{config('mail.fronturl')}}/invitation/{{$type}}/{{$token}}">click this link</a> and login or create an account.
</body>
</html>