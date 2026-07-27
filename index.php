<?php
session_start();

if (isset($_SESSION['id_usuario'])) {
    header("Location: panel_principal.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gestión Académica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="frontend/css/style.css">
</head>
<body class="d-flex align-items-center" style="min-height: 100vh;">

<div class="login-container w-100">
    <h2 class="text-center mb-4">🎓 Iniciar Sesión</h2>

    <?php
    if (isset($_GET['error'])) {
        $mensajes = [
            'vacio' => 'Debes ingresar usuario y contraseña.',
            'db'    => 'No se pudo conectar con el sistema. Intenta de nuevo.',
            '1'     => 'Usuario o contraseña incorrectos.',
        ];
        $clave = $_GET['error'];
        echo '<div class="alert alert-danger">' .
            htmlspecialchars($mensajes[$clave] ?? 'Usuario o contraseña incorrectos.') .
            '</div>';
    }
    ?>

    <form action="backend/procesar_login.php" method="POST">
        <div class="mb-3">
            <label for="usuario" class="form-label fw-semibold">Usuario</label>
            <input type="text" id="usuario" name="usuario" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label fw-semibold">Contraseña</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-guardar text-white w-100">Ingresar</button>
    </form>
</div>

</body>
</html>
