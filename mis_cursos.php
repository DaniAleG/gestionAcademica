<?php
session_start();
require_once 'includes/helpers.php';
requerirSesion();
requerirRol(['maestro']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Cursos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="frontend/css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include 'includes/sidebar.php'; ?>

        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4 p-3">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h4 text-secondary">Mis Cursos</span>
                    <a href="panel_principal.php" class="btn btn-outline-secondary btn-sm">&larr; Panel Principal</a>
                </div>
            </nav>

            <div class="container-fluid px-4 pb-4">
                <div id="zona-mensajes"></div>

                <!-- Lista de cursos -->
                <div class="card shadow-sm mb-4" id="vista-lista">
                    <div class="card-header bg-white fw-semibold">Asignaturas que dictas</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-azul">
                                    <tr>
                                        <th>Asignatura</th>
                                        <th>Créditos</th>
                                        <th>Titulación</th>
                                        <th class="text-center">Ver alumnos y notas</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla-cursos">
                                    <tr><td colspan="4" class="text-center text-secondary py-4">Cargando...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Detalle: alumnos y notas de un curso -->
                <div class="card shadow-sm d-none" id="vista-detalle">
                    <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                        <span id="titulo-detalle">Alumnos</span>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="volverALista()">&larr; Volver a mis cursos</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-azul">
                                    <tr>
                                        <th>Alumno</th>
                                        <th>Cédula</th>
                                        <th class="text-center">Parcial</th>
                                        <th class="text-center">Final</th>
                                        <th class="text-center">Supletorio</th>
                                        <th class="text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla-notas">
                                    <tr><td colspan="6" class="text-center text-secondary py-4">Selecciona un curso.</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted small mt-2 mb-0">Escribe una nota entre 0 y 10 y presiona Enter (o haz clic fuera del campo) para guardarla.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script src="frontend/js/api-utils.js"></script>
<script src="frontend/js/validaciones.js"></script>
<script src="frontend/js/mis_cursos.js"></script>
</body>
</html>
