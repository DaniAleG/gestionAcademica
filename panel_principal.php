<?php
session_start();
require_once __DIR__ . '/includes/helpers.php';
requerirSesion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Principal - Gestión Académica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="frontend/css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include 'includes/sidebar.php'; ?>

        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4 p-3">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h4 text-secondary">Panel Principal</span>
                    <div class="d-flex align-items-center">
                        <span class="me-4 fw-bold" style="color: var(--azul-oscuro);">
                            👤 <?php echo htmlspecialchars(strtoupper($_SESSION['usuario'])); ?>
                        </span>
                        <a href="backend/logout.php" class="btn btn-outline-danger btn-sm">Cerrar Sesión</a>
                    </div>
                </div>
            </nav>

            <div class="container-fluid px-4">
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 card-acento">
                            <div class="card-body py-5 text-center">
                                <h2 style="color: var(--azul-oscuro);">Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></h2>
                                <?php $rolActual = $_SESSION['rol'] ?? ''; ?>
                                <?php if ($rolActual === 'admin'): ?>
                                    <p class="text-muted fs-5 mt-3">Selecciona una opción del menú lateral para comenzar a gestionar los registros.</p>
                                <?php elseif ($rolActual === 'maestro'): ?>
                                    <p class="text-muted fs-5 mt-3">Desde <strong>Mis Cursos</strong> puedes ver a tus alumnos y registrar sus calificaciones.</p>
                                    <a href="mis_cursos.php" class="btn btn-guardar text-white mt-2">Ir a Mis Cursos</a>
                                <?php elseif ($rolActual === 'alumno'): ?>
                                    <p class="text-muted fs-5 mt-3">Desde <strong>Mis Materias</strong> puedes ver tus calificaciones y si aprobaste cada materia.</p>
                                    <a href="mis_materias.php" class="btn btn-guardar text-white mt-2">Ir a Mis Materias</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="frontend/js/validaciones.js"></script>
</body>
</html>
