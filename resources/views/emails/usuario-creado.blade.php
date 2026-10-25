<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Tus datos de acceso</title>
</head>
<body>
    <p>Hola {{ $usuario->name }},</p>

    <p>Estos son tus datos para ingresar:</p>
    <p><strong>Correo:</strong> {{ $usuario->email }}</p>
    <p><strong>Contraseña:</strong> {{ $password }}</p>

    <p><a href="{{ config('app.url') }}">Ingresar al sistema</a></p>
    <p>Por seguridad, no compartas esta contraseña y cambiala después de iniciar sesión.</p>
</body>
</html>
