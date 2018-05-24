<html>
<head>
    <style>

    </style>
</head>

<body>
    <h1>Join {{$name}} on Informed 365</h1>
    <p>You are invited to the {{$type}} {{$name}} as a {{$role}} by {{$user_name}}.</p>
    <a href="{{config('mail.fronturl')}}/invitation/{{$type}}/{{$token}}">View invitation</a>
</body>
</html>